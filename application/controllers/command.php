<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * CMD controller
 *
 * Requires authentication. See the helper nagioscmd for more info.
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Command_Controller extends Authenticated_Controller
{
	private $command_id = false;
	private $cmd_params = array();
	private $csrf_token = false;

	/**
	 * Initializes a page with the correct view, java-scripts and css
	 * @param $view The name of the theme-page we want to print
	 */
	protected function init_page($view)
	{
		$this->template->content = $this->add_view($view);
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->template->disable_refresh = true;
	}

	protected function get_array_var($ary, $k, $dflt = false)
	{
		if (is_array($k)) {
			if (count($k) === 1)
				$k = array_pop($k);
		}

		if (is_array($k))
			return false;

		if (isset($ary[$k]))
			return $ary[$k];

		return $dflt;
	}

	/**
	 * Create a standard checkbox item
	 * @param $name The user visible text for this option
	 * @param $dflt The default value of the checkbox (true = checked)
	 * @return array suitable for passing to the request template
	 */
	protected function cb($name, $dflt = true)
	{
		return array('type' => 'checkbox', 'name' => $name, 'default' => $dflt);
	}

	/**
	 * Request a command to be submitted
	 * This method prints input fields to be selected for the
	 * named command.
	 * @param $name The requested command to run
	 * @param $parameters The parameters (host_name etc) for the command
	 */
	public function submit($cmd = false)
	{
		$this->init_page('command/request');
		$this->xtra_js[] = $this->add_path('command/js/command.js');
		$this->template->js_header->js = $this->xtra_js;

		if ($cmd === false) {
			$cmd = $this->input->get('cmd_typ');
		}

		$params = array();
		foreach ($_GET as $k => $v) {
			switch ($k) {
			 case 'host':
			 case 'hostgroup':
			 case 'servicegroup':
				$params[$k . '_name'] = $v;
				break;
			 default:
				$params[$k] = $v;
			}
		}
		if (!$this->_is_authorized_for_command($params)) {
			url::redirect(Router::$controller.'/unauthorized');
		}

		$command = new Command_Model;
		$info = $command->get_command_info($cmd, $params);
		$param = $info['params'];
		switch ($cmd) {
		 case 'SCHEDULE_HOST_CHECK':
		 case 'SCHEDULE_SVC_CHECK':
		 case 'SCHEDULE_HOST_SVC_CHECKS':
			$param['_force'] = $this->cb('Force Check');
			break;

		 case 'PROCESS_HOST_CHECK_RESULT':
		 case 'PROCESS_SERVICE_CHECK_RESULT':
			$param['_perfdata'] = array
				('type' => 'string',
				 'size' => 100,
				 'name' => 'Performance data');
			break;

		 case 'SCHEDULE_HOST_DOWNTIME':
			$param['_child-hosts'] = array
				('type' => 'select',
				 'options' => array
				 ('none' => 'Do nothing',
				  'triggered' => 'Schedule triggered downtime',
				  'fixed' => 'Schedule fixed downtime'),
				 'default' => 'triggered',
				 'name' => 'Child Hosts');
			# fallthrough
		 case 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME':
			$param['_services-too'] = $this->cb('Schedule downtime for services too');
			break;

		 case 'SEND_CUSTOM_SVC_NOTIFICATION':
		 case 'SEND_CUSTOM_HOST_NOTIFICATION':
			$param['_broadcast'] = $this->cb('Broadcast');
			$param['_force'] = $this->cb('Force notification');
			$param['_increment'] = $this->cb('Increment notification number');
			break;

		 case 'ENABLE_HOST_SVC_CHECKS':
		 case 'DISABLE_HOST_SVC_CHECKS':
			$en_dis = $cmd{0} === 'E' ? 'Enable' : 'Disable';
			$param['_host-too'] = $this->cb($en_dis . ' checks for host too');
			break;

		 case 'ENABLE_HOST_CHECK':
		 case 'DISABLE_HOST_CHECK':
			$en_dis = $cmd{0} === 'E' ? 'Enable' : 'Disable';
			$param['_services-too'] = $this->cb($en_dis . ' checks for services too');
			break;
		}
		$info['params'] = $param;

		$this->template->content->requested_command = $cmd;
		$this->template->content->info = $info;

		if (is_array($info)) foreach ($info as $k => $v) {
			$this->template->content->$k = $v;
		}
	}

	/**
	 * Takes the command parameters given by the "submit" function
	 * and creates a Nagios command that gets fed to Nagios through
	 * the external command pipe.
	 */
	public function commit()
	{
		$this->init_page('command/commit');
		$cmd = $_REQUEST['requested_command'];
		$this->template->content->requested_command = $cmd;

		$nagios_commands = array();
		$param = $this->get_array_var($_REQUEST, 'cmd_param', array());
		if (!$this->_is_authorized_for_command($param, $cmd)) {
			url::redirect(Router::$controller.'/unauthorized');
		}

		if (isset($param['comment']) && trim($param['comment'])=='') {
			# comments shouldn't ever be empty
			$this->template->content->result = false;
			$this->template->content->error = $this->translate->_("Required field 'Comment' was not entered").'<br />'.
			$this->translate->_(sprintf('Go %s back %s and verify that you entered all required information correctly', '<a href="javascript:history.back();">', '</a>'));
			return false;
		}
		switch ($cmd) {
		 case 'SCHEDULE_HOST_CHECK':
		 case 'SCHEDULE_SVC_CHECK':
		 case 'SCHEDULE_HOST_SVC_CHECKS':
			if (!empty($param['_force'])) {
				unset($param['force']);
				$cmd = 'SCHEDULE_FORCED' . substr($cmd, strlen("SCHEDULE"));
			}
			break;

		 case 'PROCESS_HOST_CHECK_RESULT':
		 case 'PROCESS_SERVICE_CHECK_RESULT':
			if (!empty($param['_perfdata']) && !empty($param['plugin_output'])) {
				$param['plugin_output'] .= "|$param[perfdata]";
				unset($param['perfdata']);
			}
			break;

		 case 'SCHEDULE_HOST_DOWNTIME':
			if (!empty($param['_child-hosts']) && $param['_child-hosts'] != 'none') {
				$what = $param['_child-hosts'];
				unset($param['_child-hosts']);
				$fixed = isset($param['fixed']) ? $param['fixed'] : false;
				if ($what === 'triggered') {
					$param['fixed'] = 0;
					$xcmd = 'SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME';
				} elseif ($what === 'fixed') {
					$param['fixed'] = 1;
					$xcmd = 'SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME';
				}
				$nagios_commands[] = nagioscmd::build_command($xcmd, $param);
				$param['fixed'] = $fixed;
			}
			# fallthrough to services-too handling
		 case 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME':
			if (!empty($param['_services-too'])) {
				unset($param['_services-too']);
				if ($cmd === 'SCHEDULE_HOST_DOWNTIME')
					$nagios_commands[] = nagioscmd::build_command('SCHEDULE_HOST_SVC_DOWNTIME', $param);
				else
					$nagios_commands[] = nagioscmd::build_command('SCHEDULE_HOSTGROUP_SVC_DOWNTIME', $param);
			}
			break;

		 case 'SEND_CUSTOM_HOST_NOTIFICATION':
		 case 'SEND_CUSTOM_SVC_NOTIFICATION':
			$options = 0;
			if (isset($param['_broadcast'])) {
				unset($param['_broadcast']);
				$options |= 1;
			}
			if (isset($param['_force'])) {
				unset($param['_force']);
				$options |= 2;
			}
			$param['options'] = $options;
			break;

		 case 'ENABLE_HOST_SVC_CHECKS':
		 case 'DISABLE_HOST_SVC_CHECKS':
			$xcmd = $cmd{0} === 'D' ? 'DISABLE' : 'ENABLE';
			$xcmd .= '_HOST_CHECKS';
			if (!empty($param['_host-too']))
				$nagios_commands[] = nagioscmd::build_command($xcmd, $param);
			break;

		 case 'ENABLE_HOST_CHECK':
		 case 'DISABLE_HOST_CHECK':
			$xcmd = $cmd{0} === 'D' ? 'DISABLE' : 'ENABLE';
			$xcmd .= '_HOST_SVC_CHECKS';
			if (!empty($param['_services-too']))
				$nagios_commands[] = nagioscmd::build_command($xcmd, $param);
			break;
		}

		$nagios_commands[] = nagioscmd::build_command($cmd, $param);

		$nagios_base_path = Kohana::config('config.nagios_base_path');
		$pipe = $nagios_base_path."/var/rw/nagios.cmd";
		$nagconfig = System_Model::parse_config_file("nagios.cfg");
		if (isset($nagconfig['command_file'])) {
			$pipe = $nagconfig['command_file'];
		}

		while ($ncmd = array_pop($nagios_commands)) {
			$this->template->content->result = nagioscmd::submit_to_nagios($ncmd, $pipe);
		}
	}

	/**
	 * Display "You're not authorized" message
	 */
	public function unauthorized()
	{
		$this->template->content = $this->add_view('command/unauthorized');
		$this->template->content->error_message = $this->translate->_('You are not authorized to submit the specified command.');
		$this->template->content->error_description = $this->translate->_('Read the section of the documentation that deals with authentication and authorization in the CGIs for more information.');
		$this->template->content->return_link_lable = $this->translate->_('Return from whence you came');
	}

	/**
	* Translated helptexts for this controller
	*/
	public static function _helptexts($id)
	{
		$translate = zend::instance('Registry')->get('Zend_Translate');

		# No helptexts defined yet - this is just an example
		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array(
			'triggered_by' => $translate->_("With triggered downtime the start of the downtime ".
				"is triggered by the start of some other scheduled host or service downtime")
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		}
		else
			echo sprintf($translate->_("This helptext ('%s') is yet not translated"), $id);
	}

	/**
	 * Check if user is authorized for the selected command
	 * http://nagios.sourceforge.net/docs/3_0/configcgi.html controls
	 * the correctness of this method
	 */
	public function _is_authorized_for_command($params = false, $cmd = false)
	{
		$cmd = isset($params['cmd_typ']) ? $params['cmd_typ'] : $cmd;

		if (empty($cmd)) {
			return false;
		}

		# first see if this is a contact and, if so, if that contact
		# is allowed to submit commands. If it isn't, we can bail out
		# early.
		$contact = Contact_Model::get_contact();
		if (!empty($contact)) {
			$contact = $contact->current();
			if (!$contact->can_submit_commands) {
				return false;
			}
		}

		# second we check if this contact is allowed to submit
		# the type of command we're looking at and, if so, if
		# we can bypass fetching all the objects we're authorized
		# to see
		$auth = new Nagios_auth_Model();
		if (!strstr($cmd, '_HOST_')) {
			if ($auth->command_hosts_root) {
				return true;
			}
			if (!$auth->authorized_for_host_commands) {
				return false;
			}
		} elseif (!strstr($cmd, '_SVC_')) {
			if ($auth->command_services_root) {
				return true;
			}
			if (!$auth->authorized_for_service_commands) {
				return false;
			}
		} else {
			# must be a system command
			if ($auth->authorized_for_system_commands) {
				return true;
			}

			return false;
		}

		# not authorized from cgi.cfg, and not a configured contact,
		# so bail out early
		if (empty($contact))
			return false;

		$service = isset($params['service']) ? $params['service'] : false;
		$host_name = isset($params['host_name']) ? $params['host_name'] : false;
		if (strstr($service, ';')) {
			# we have host_name;service in service field
			$parts = explode(';', $service);
			if (!empty($parts) && sizeof($parts)==2) {
				$service = $parts[1];
				$host_name = $parts[0];
			}
		}

		# FIXME handle host/servicegroup commands as well

		# neither host_name nor service description. Either the user
		# hasn't filled out the form yet, or this regards hostgroups
		# or servicegroups
		if (!$service && !$host_name) {
			return true;
		}

		# if the user isn't specifically configured for the service, he/she
		# can still submit commands for it if he/she is a contact for the host
		if ($service) {
			$auth->get_authorized_services_r();
			if (isset($auth->services_r[$host_name . ';' . $service])) {
				return true;
			}
		}

		if ($host_name) {
			$auth->get_authorized_hosts_r();
			if (isset($auth->hosts_r[$host_name])) {
				return true;
			}
		}
		return false;
	}
}
