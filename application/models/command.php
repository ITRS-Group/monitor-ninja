<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Sends external commands to Nagios command FIFO
 */
class Command_Model extends Model
{
	private $auth = false;
	protected $dryrun = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	protected function get_object_list($param_name)
	{
		$ary = false;
		switch ($param_name) {
		 case 'host_name':
			$ary = $this->auth->get_authorized_hosts();
			break;
		 case 'service':
		 case 'service_description':
			$ary = $this->auth->get_authorized_services();
			break;
		 case 'hostgroup_name':
			$ary = $this->auth->get_authorized_hostgroups();
			break;
		 case 'servicegroup_name':
			$ary = $this->auth->get_authorized_servicegroups();
			break;
		}


		if ($ary) {
			$ret_ary = array();
			foreach ($ary as $k => $v) {
				$ret_ary[$v] = $v;
			}
			return $ret_ary;
		}

		if ($this->dryrun)
			return array(1 => 'random object');

		$obj_type = substr($param_name, 0, -5);
		$query = "SELECT $param_name FROM $obj_type";
		return $this->db->query($query);
	}

	protected function get_comment_ids($command_name = 'DEL_HOST_COMMENT')
	{
		if ($this->dryrun)
			return array(1);

		$type = 'host';
		if ($command_name != 'DEL_HOST_COMMENT') {
			$type = 'service';
		}
		$query = 'SELECT comment_id, host_name, service_description ' .
			'FROM comment WHERE service_description ';
		if ($type === 'host') {
			$objs = $this->auth->get_authorized_hosts_r();
			$query = 'SELECT comment_id, host_name as objname FROM comment ' .
				"WHERE service_description = ''";
		} else {
			$objs = $this->auth->get_authorized_services_r();
			$query = "SELECT comment_id, " .
				"concat(host_name, ';', service_description) as objname " .
				"FROM comment WHERE service_description != ''";
		}

		$result = $this->db->query($query);
		$ret = array();
		foreach ($result as $ary) {
			if (isset($objs[$ary->objname])) {
				$ret[$ary->comment_id] = $ary->comment_id;
			}
		}
		return $ret;
	}

	protected function get_downtime_ids($command_name, $defaults=false)
	{
		$host_name = isset($defaults['host_name']) ? $defaults['host_name'] : false;
		$service = isset($defaults['service']) ? $defaults['service'] : false;
		if (empty($host_name)) {
			return false;
		}

		$translate = zend::instance('Registry')->get('Zend_Translate');
		$na_str = $translate->_('N/A');
		$options = false;
		$options = array(0 => $na_str);
		$downtime_data = Downtime_Model::get_downtime_data($host_name, $service);
		if ($downtime_data !== false) {
			foreach ($downtime_data as $data) {
				if (strstr($command_name, 'HOST_DOWNTIME')) {
					$options[$data->downtime_id] = $translate->_(sprintf("ID: %s, Host '%s' starting @ %s\n", $data->downtime_id, $data->host_name, date(nagstat::date_format(), $data->start_time)));
				} elseif (strstr($command_name, 'SVC_DOWNTIME')) {
					if (!empty($data->service_description))
						$options[$data->downtime_id] = sprintf("ID: %s, Service '%s' on host '%s' starting @ %s \n", $data->downtime_id, $data->service_description, $data->host_name, date(nagstat::date_format(), $data->start_time));
				}
			}
		}

		return $options;
	}

	/**
	 * Obtain command information
	 * Complete with information and data needed to request input
	 * regarding a particular command.
	 *
	 * @param $cmd The name (or 'id') of the command
	 * @param $defaults Default values for command parameters
	 * @param $dryrun Testing variable. Ignore.
	 * @return Indexed array
	 */
	public function get_command_info($cmd, $defaults = false, $dryrun = false)
	{
		$this->dryrun = $dryrun;

		$info = nagioscmd::cmd_info($cmd);
		# we need the template to get the information we need
		if (empty($info) || !isset($info['template'])) {
			return false;
		}
		$translate = zend::instance('Registry')->get('Zend_Translate');

		$cmd = $info['name'];

		$raw_params = array_slice(explode(';', $info['template']), 1);
		$params = array();
		$ary = false;
		foreach ($raw_params as $param_name) {
			# reset between each loop
			$ary = array();
			$suffix = substr($param_name, -5);

			switch ($param_name) {
			 case 'author':
				$ary = array('type' => 'immutable', 'default' => $this->auth->user);
				break;
			 case 'check_attempts':
				$ary = array('type' => 'int', 'default' => 5);
				break;
			 case 'check_interval':
				$ary = array('type' => 'int', 'default' => 300);
				break;
			 case 'comment':
				$ary = array('type' => 'string', 'size' => 100);
				break;
			 case 'comment_id':
				$ary = array('type' => 'select', 'options' => $this->get_comment_ids($cmd));
				break;
			 case 'delete':
				$ary = array('type' => 'bool', 'default' => 1);
				break;
			 case 'downtime_id':
			 case 'trigger_id':
				$ary = array('type' => 'select', 'options' => $this->get_downtime_ids($cmd, $defaults));
				$ary['name'] = $translate->_('Triggered By');
				$ary['help'] = help::render('triggered_by');
				break;
			 case 'duration':
				$ary = array('type' => 'duration', 'default' => '2.0');
				break;
			 case 'event_handler_command':
				# FIXME: stub options
				$ary = array('mixed' => array('select', 'string'), 'options' => array());
				break;
			 case 'file_name':
				$ary = array('type' => 'string');
				break;
			 case 'fixed':
				$ary = array('type' => 'bool', 'default' => 1);
				break;
			 case 'notification_number':
				$ary = array('type' => 'int', 'default' => 1);
				break;
			 case 'notify':
				$ary = array('type' => 'bool', 'default' => 1);
				break;
			 case 'options':
				$ary = 'skip';
				break;
			 case 'persistent':
				$ary = array('type' => 'bool', 'default' => 1);
				break;
			 case 'plugin_output':
				$ary = array('type' => 'string', 'size' => 100);
				break;
			 case 'return_code':
				$ary = array('type' => 'select', 'options' => array
							 (0 => 'OK', 1 => 'Warning', 2 => 'Critical',
							  3 => 'Unknown'));
				break;
			 case 'status_code':
				$ary = array('type' => 'select', 'options' => array
							 (0 => 'Up', 1 => 'Down'));
				break;
			 case 'sticky':
				$ary = array('type' => 'bool', 'default' => 1);
				break;
			 case 'value':
				$ary = array('type' => 'string', 'size' => 100, 'default' => 'variable=value');
				break;
			 case 'varname':
			 case 'varvalue':
				$ary = array('type' => 'string', 'size' => 100);
				$ary['name'] = $translate->_(sprintf('Variable %s', ucfirst(substr($param_name, 3))));
				break;
			# nearly all the object link parameters are handled the same
			# way (more or less), so we just clump them together here
			 case 'service':
			 case 'service_description':
				$ary['name'] = 'Service';
				# fallthrough
			 case 'servicegroup_name':
			 case 'contact_name':
			 case 'contactgroup_name':
			 case 'host_name':
			 case 'hostgroup_name':
				if (!isset($ary['name']))
					$ary['name'] = ucfirst(substr($param_name, 0, -5));
			 case 'timeperiod':
				if (!isset($ary['name']))
					$ary['name'] = $translate->_('Timeperiod');
			 case 'notification_timeperiod':
				if (!isset($ary['name']))
					$ary['name'] = $translate->_('Notification Timeperiod');
			 case 'check_timeperiod':
				if (!isset($ary['name']))
					$ary['name'] = $translate->_('Check Timeperiod');
				$ary['type'] = 'select';
				$ary['options'] = $this->get_object_list($param_name);
				break;
			 case 'notification_delay':
				$ary = array('type' => 'int', 'default' => 5);
				$ary['name'] = $translate->_('Notification delay (in minutes)');
				break;
			# same go for *_time parameters
			 case 'check_time':
			 case 'end_time':
			 case 'notification_time':
			 case 'start_time':
				$ary = array('type' => 'time', 'default' => date(nagstat::date_format(), time()));
				if ($param_name === 'end_time')
					$ary['default'] = date(nagstat::date_format(), time() + 7200);
				break;
			}

			if ($ary === 'skip')
				continue;

			if (!isset($ary['name'])) {
				if (strpos($param_name, '_') !== false) {
					$foo = explode('_', $param_name);
					$name = '';
					foreach ($foo as $name_part) {
						$name .= ucfirst($name_part) . ' ';
					}
					$ary['name'] = trim($name);
				} else {
					$ary['name'] = ucfirst($param_name);
				}
			}

			if (isset($defaults[$param_name])) {
				if ($param_name === 'service' && isset($defaults['host_name'])) {
					$ary['default'] = $defaults['host_name'] . ';' . $defaults[$param_name];
				} else {
					$ary['default'] = $defaults[$param_name];
				}
			}

			$params[$param_name] = $ary;
		}

		$info['params'] = $params;
		return $info;
	}
}
