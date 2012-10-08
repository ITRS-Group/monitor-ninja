<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Sends external commands to Nagios command FIFO
 */
class Command_Model extends Model
{
	private $auth = false;
	protected $dryrun = false; /**< Set to true to make it not actually do anything */

	public function __construct()
	{
		parent::__construct();
		$this->auth = Nagios_auth_Model::instance();
	}

	/**
	 * Get the users/systems configured value for this option
	 * @param $setting Option name
	 */
	public function get_setting($setting)
	{
		# the underscore is an implementation detail ("don't pass this straight
		# to nagios") that should not be exposed in config/nagdefault.php
		$setting = 'nagdefault.'.ltrim($setting, '_');
		$value = config::get($setting, '*', false, true);
		return $value;
	}

	/**
	 * Get all objects for this "param name" (which is almost object type, but not quite)
	 */
	protected function get_object_list($param_name)
	{
		$ary = false;
		switch ($param_name) {
		 case 'host_name':
			$ary = Livestatus::instance()->getHosts(array('columns' => array('name')));
			break;
		 case 'service':
		 case 'service_description':
			$ary = Livestatus::instance()->getServices(array('columns' => array('name')));
			break;
		 case 'hostgroup_name':
			$ary = Livestatus::instance()->getHostgroups(array('columns' => array('name')));
			break;
		 case 'servicegroup_name':
			$ary = Livestatus::instance()->getServicegroups(array('columns' => array('name')));
			break;
		}


		if ($ary) {
			$ret_ary = array();
			foreach ($ary as $v) {
				$ret_ary[$v['name']] = $v['name'];
			}
			ksort($ret_ary);
			return $ret_ary;
		}

		if ($this->dryrun)
			return array(1 => 'random object');

		$obj_type = substr($param_name, 0, -5);
		$query = "SELECT $param_name FROM $obj_type";
		return $this->db->query($query);
	}

	/**
	 * Get ids of current comments
	 *
	 * @param $command_name Name of the command (determines which id's to get)
	 * @return array(id => object_name);
	 */
	public function get_comment_ids($command_name = 'DEL_HOST_COMMENT')
	{
		if ($this->dryrun)
			return array(1);

		if ($command_name != 'DEL_HOST_COMMENT') {
			$query = "SELECT comment_id, ".
				sql::concat('host_name', ';', 'service_description').
				" AS obj_name FROM comment_tbl WHERE service_description != '' OR service_description is not NULL";
		} else {
			$query = 'SELECT comment_id, host_name as objname FROM comment_tbl ' .
				"WHERE (service_description = '' OR service_description IS NULL)";
		}

		$result = $this->db->query($query);
		$ret = array();
		foreach ($result as $ary) {
			$ret[$ary->comment_id] = $ary->comment_id;
		}
		return $ret;
	}

	/**
	 * Get all downtime IDs
	 */
	protected function get_downtime_ids($command_name, $defaults=false)
	{
		$host_name = isset($defaults['host_name']) ? $defaults['host_name'] : false;
		$service = isset($defaults['service']) ? $defaults['service'] : false;

		$options = false;
		$options = array(0 => _('N/A'));
		$downtime_data = Downtime_Model::get_downtime_data();
		if ($downtime_data !== false) {
			foreach ($downtime_data as $data) {
				if (strstr($command_name, 'HOST_DOWNTIME')) {
					$options[$data['id']] = _(sprintf("ID: %s, Host '%s' starting @ %s\n", $data['id'], $data['host_name'], date(nagstat::date_format(), $data['start_time'])));
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
	 * @param $cmd string The name (or 'id') of the command
	 * @param $defaults array = false Default values for command parameters
	 * @param $dryrun boolean = false Testing variable. Ignore.
	 * @return false|indexed array
	 */
	public function get_command_info($cmd, $defaults = false, $dryrun = false)
	{
		$this->dryrun = $dryrun;

		$info = nagioscmd::cmd_info($cmd);
		# we need the template to get the information we need
		if (empty($info) || !isset($info['template'])) {
			return false;
		}

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
				$ary = array('type' => 'int', 'default' => $this->get_setting('check_attempts'));
				break;
			 case 'check_interval':
				$ary = array('type' => 'int', 'default' => $this->get_setting('check_interval'));
				break;
			 case 'comment':
				$ary = array('type' => 'string', 'size' => 100, 'default' => $this->get_setting('comment'));
				break;
			 case 'comment_id':
				$ary = array('type' => 'select', 'options' => $this->get_comment_ids($cmd));
				if (isset($defaults['com_id'])) {
					$ary['default'] = $defaults['com_id'];
				}
				break;
			 case 'delete':
				$ary = array('type' => 'bool', 'default' => $this->get_setting('delete'));
				break;
			 case 'downtime_id':
			 case 'trigger_id':
				$ary = array('type' => 'select', 'options' => $this->get_downtime_ids($cmd, $defaults));
				if (isset($defaults['service']) && is_array($defaults['service'])) {
					$downtime_data = Downtime_Model::get_downtime_data(nagstat::SERVICE_DOWNTIME);
					foreach ($downtime_data as $downtime)
						if (in_array($downtime->host_name . ';' . $downtime->service_description, $defaults['service']))
							$ary['default'][] = $downtime->downtime_id;
				}
				if (isset($defaults['host_name']) && is_array($defaults['host_name'])) {
					$downtime_data = Downtime_Model::get_downtime_data(nagstat::HOST_DOWNTIME);
					foreach ($downtime_data as $downtime)
						if (in_array($downtime->host_name, $defaults['host_name']))
							$ary['default'][] = $downtime->downtime_id;
				}
				$ary['name'] = _('Triggered By');
				$ary['help'] = help::render('triggered_by');
				break;
			 case 'duration':
				$ary = array('type' => 'duration', 'default' => $this->get_setting('duration'));
				$ary['help'] = help::render('duration');
				break;
			 case 'event_handler_command':
				# FIXME: stub options
				$ary = array('mixed' => array('select', 'string'), 'options' => array());
				break;
			 case 'file_name':
				$ary = array('type' => 'string');
				break;
			 case 'fixed':
				$ary = array('type' => 'bool', 'default' => $this->get_setting('fixed'));
				break;
			 case 'notification_number':
				$ary = array('type' => 'int', 'default' => 1);
				break;
			 case 'notify':
				$ary = array('type' => 'bool', 'default' => $this->get_setting('notify'));
				break;
			 case 'options':
				$ary = 'skip';
				break;
			 case 'persistent':
				$ary = array('type' => 'bool', 'default' => $this->get_setting('persistent'));
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
				$ary = array('type' => 'bool', 'default' => $this->get_setting('sticky'));
				break;
			 case 'value':
				$ary = array('type' => 'string', 'size' => 100, 'default' => 'variable=value');
				break;
			 case 'varname':
			 case 'varvalue':
				$ary = array('type' => 'string', 'size' => 100);
				$ary['name'] = _(sprintf('Variable %s', ucfirst(substr($param_name, 3))));
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
				if (!isset($ary['name'])) {
					$ary['name'] = ucfirst(substr($param_name, 0, -5));
				}
			 case 'timeperiod':
				if (!isset($ary['name'])) {
					$ary['name'] = _('Timeperiod');
				}
			 case 'notification_timeperiod':
				if (!isset($ary['name'])) {
					$ary['name'] = _('Notification Timeperiod');
				}
			 case 'check_timeperiod':
				if (!isset($ary['name'])) {
					$ary['name'] = _('Check Timeperiod');
				}
				$ary['type'] = 'select';
				if($defaults) {
					if(isset($defaults['host_name'])) {
						if(isset($defaults['service'])) {
							if($this->auth->is_authorized_for_service($defaults['host_name'], $defaults['service'])) {
								$ary['options'] = array($defaults['host_name'].":".$defaults['service']);
							}
						} elseif($this->auth->is_authorized_for_host($defaults['host_name'])) {
							$ary['options'] = array($defaults['host_name']);
						}
					} elseif(isset($defaults['hostgroup_name']) && $this->auth->is_authorized_for_hostgroup($defaults['hostgroup_name'])) {
						$ary['options'] = array($defaults['hostgroup_name']);
					} elseif(isset($defaults['servicegroup_name']) && $this->auth->is_authorized_for_servicegroup($defaults['servicegroup_name'])) {
						$ary['options'] = array($defaults['servicegroup_name']);
					}
				}
				if(!isset($ary['options'])) {
					$ary['options'] = $this->get_object_list($param_name, $defaults);
				}
				if(count($ary['options']) == 1 && (!is_array($ary['options']) || count(current($ary['options'])) == 1)) {
					// Must check for inner array's length since the same method call is
					// used by both "single" and "multiple" versions of submitting commands
					$ary['type'] = 'immutable';
				}
				break;
			 case 'notification_delay':
				$ary = array('type' => 'int', 'default' => 5);
				$ary['name'] = _('Notification delay (in minutes)');
				break;
			# same go for *_time parameters
			 case 'check_time':
			 case 'end_time':
			 case 'notification_time':
			 case 'start_time':
				$ary = array('type' => 'time', 'default' => date(nagstat::date_format(), time()+10));
				if ($param_name === 'end_time')
					$ary['default'] = date(nagstat::date_format(), time() + ($this->get_setting('duration') * 3600) + 10);
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
