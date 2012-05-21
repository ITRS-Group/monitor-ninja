<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * CMD controller
 *
 * Requires authentication. See the helper nagioscmd for more info.
 *
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
	 * @var int = 300, if the starting time of a scheduled downtime is
	 * older than this many seconds, it's considered to have been added retrospectively
	 */
	private $grace_time_in_s = 300;
	private $objects = false;
	private $obj_type = false;

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
 	 * @param string $submitted_start_time (Y-m-d H:i:s)
 	 * @param string $submitted_end_time (Y-m-d H:i:s)
	 * @return true | string (string = error message)
	 */
	private function _validate_dates($submitted_start_time, $submitted_end_time) {
		$start_time = strtotime($submitted_start_time);
		$end_time = strtotime($submitted_end_time);
		$errors = array();
		if(!$start_time || !$end_time) {
			if(!$start_time && !$end_time) {
				return "Neither of your submitted dates are valid, please <a href='javascript:history.back();'>adjust them</a>";
			} else {
				return sprintf("%s is not a valid date, please <a href='javascript:history.back();'>adjust it</a>", $start_time ? $submitted_end_time : $submitted_start_time);
			}
		}
		if($start_time > $end_time) {
			return sprintf("The downtime can not end before it starts. Please <a href='javascript:history.back();'>adjust it</a>", $submitted_start_time);
		}
		return true;
	}

	/**
	 * Create a standard checkbox item
	 * @param $description The user visible text for this option
	 * @param $name The internal name for this option
	 * @return array suitable for passing to the request template
	 */
	protected function cb($description, $name)
	{
		$value = Command_Model::get_setting($name);
		return array('type' => 'checkbox', 'name' => $description, 'default' => $value);
	}

	/**
	 * Request a command to be submitted
	 * This method prints input fields to be selected for the
	 * named command.
	 * @param $name The requested command to run
	 * @param $parameters The parameters (host_name etc) for the command
	 */
	public function submit($cmd = false, $inparams=false)
	{
		$this->init_page('command/request');
		$this->xtra_js[] = $this->add_path('command/js/command.js');
		$this->template->js_header->js = $this->xtra_js;

		if ($cmd === false) {
			$cmd = $this->input->get('cmd_typ');
		}

		$params = array();
		if ($inparams === false) {
			$inparams = $_GET;
		}

		foreach ($inparams as $k => $v) {
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
		$auth_check = $this->_is_authorized_for_command($params);
		if ($auth_check === false || $auth_check <0 ) {
			url::redirect(Router::$controller.'/unauthorized/'.$auth_check);
		}

		$command = new Command_Model;
		$info = $command->get_command_info($cmd, $params);
		$param = $info['params'];
		switch ($cmd) {
		 case 'SCHEDULE_HOST_CHECK':
		 case 'SCHEDULE_SVC_CHECK':
		 case 'SCHEDULE_HOST_SVC_CHECKS':
			$param['_force'] = $this->cb($this->translate->_('Force Check'), '_force');
			break;

		 case 'PROCESS_HOST_CHECK_RESULT':
		 case 'PROCESS_SERVICE_CHECK_RESULT':
			$param['_perfdata'] = array
				('type' => 'string',
				 'size' => 100,
				 'name' => $this->translate->_('Performance data'));
			break;

		 case 'SCHEDULE_HOST_DOWNTIME':
			$this->template->inline_js = "grace_time_in_s = '$this->grace_time_in_s'";
			$this->xtra_js[] = $this->add_path('command/js/schedule_downtime.js');
			$param['_child-hosts'] = array
				('type' => 'select',
				 'options' => array
				 ('none' => $this->translate->_('Do nothing'),
				  'triggered' => $this->translate->_('Schedule triggered downtime'),
				  'fixed' => $this->translate->_('Schedule fixed downtime')),
				 'default' => 'triggered',
				 'name' => $this->translate->_('Child Hosts'));
			# fallthrough
		 case 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME':
			break;
		 case 'SCHEDULE_SVC_DOWNTIME':
			$this->template->inline_js = "grace_time_in_s = '$this->grace_time_in_s'";
			$this->xtra_js[] = $this->add_path('command/js/schedule_downtime.js');
			break;

		 case 'SEND_CUSTOM_SVC_NOTIFICATION':
		 case 'SEND_CUSTOM_HOST_NOTIFICATION':
			$param['_broadcast'] = $this->cb($this->translate->_('Broadcast'), '_broadcast');
			$param['_force'] = $this->cb($this->translate->_('Force notification'), '_force');
			$param['_increment'] = $this->cb($this->translate->_('Increment notification number'), '_increment');
			break;

		 case 'ENABLE_HOST_SVC_CHECKS':
		 case 'DISABLE_HOST_SVC_CHECKS':
		 case 'ENABLE_HOSTGROUP_SVC_CHECKS':
		 case 'DISABLE_HOSTGROUP_SVC_CHECKS':
		 case 'ENABLE_SERVICEGROUP_SVC_CHECKS':
		 case 'DISABLE_SERVICEGROUP_SVC_CHECKS':
			$en_dis = $cmd{0} === 'E' ? $this->translate->_('Enable') : $this->translate->_('Disable');
			$param['_host-too'] = $this->cb(sprintf($this->translate->_('%s checks for host too'), $en_dis), '_host-too');
			break;

		 case 'ENABLE_HOST_CHECK':
		 case 'DISABLE_HOST_CHECK':
			$en_dis = $cmd{0} === 'E' ? $this->translate->_('Enable') : $this->translate->_('Disable');
			$param['_services-too'] = $this->cb(sprintf($this->translate->_('%s checks for services too'), $en_dis), '_services-too');
			break;

		 case 'ENABLE_HOST_SVC_NOTIFICATIONS':
		 case 'DISABLE_HOST_SVC_NOTIFICATIONS':
			$en_dis = $cmd{0} === 'E' ? $this->translate->_('Enable') : $this->translate->_('Disable');
			$param['_host-too'] = $this->cb(sprintf($this->translate->_('%s notifications for host too'), $en_dis), '_host-too');
			break;

		 case 'ACKNOWLEDGE_HOST_PROBLEM':
			$param['_services-too'] = $this->cb($this->translate->_('Acknowledge any problems on services too'), '_services-too');
			break;
		 case 'REMOVE_HOST_ACKNOWLEDGEMENT':
			$param['_services-too'] = $this->cb($this->translate->_('Remove any acknowledgements on services too'), '_services-too');
			break;
		 case 'NACOMA_DEL_HOST':
		 case 'NACOMA_DEL_SERVICE':
			// Delete the host/service then route to NACOMA SAVE_CONFIG page
			if (isset($params['service'])) {
				foreach ($params['service'] as $service) {
					Nacoma::delService($service);
				}
			}

			if (isset($params['host_name'])) {
				foreach ($params['host_name'] as $host) {
					Nacoma::delHost($host);
				}
			}

			url::redirect('/configuration/configure?page=export.php');
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
		if(!isset($_REQUEST['requested_command'])) {
			return url::redirect(Router::$controller.'/unauthorized/');
		}
		$cmd = $_REQUEST['requested_command'];
		$this->init_page('command/commit');
		$this->template->content->requested_command = $cmd;

		$nagios_commands = array();
		$param = $this->get_array_var($_REQUEST, 'cmd_param', array());
		$auth_check = $this->_is_authorized_for_command($param, $cmd);
		if ($auth_check === false || $auth_check <0) {
			return url::redirect(Router::$controller.'/unauthorized/'.$auth_check);
		}

		if (isset($param['comment']) && trim($param['comment'])=='') {
			# comments shouldn't ever be empty
			$this->template->content->result = false;
			$this->template->content->error = $this->translate->_("Required field 'Comment' was not entered").'<br />'.
			$this->translate->_(sprintf('Go %s back %s and verify that you entered all required information correctly', '<a href="javascript:history.back();">', '</a>'));
			return false;
		}
		$fallthrough = false;
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
				$param['plugin_output'] .= "|".$param['_perfdata'];
				unset($param['perfdata']);
			}
			break;

		 case 'SCHEDULE_HOST_DOWNTIME':
			$date_validation_result = $this->_validate_dates($param['start_time'], $param['end_time']);
			if($date_validation_result !== true) {
				$this->template->content->result = false;
				$this->template->content->error = $date_validation_result;
				return;
			}
			if (!empty($param['_child-hosts']) && $param['_child-hosts'] != 'none') {
				$what = $param['_child-hosts'];
				unset($param['_child-hosts']);
				if ($what === 'triggered') {
					$cmd = 'SCHEDULE_AND_PROPAGATE_TRIGGERED_HOST_DOWNTIME';
				} elseif ($what === 'fixed') {
					$cmd = 'SCHEDULE_AND_PROPAGATE_HOST_DOWNTIME';
				}
			}
			if(1 == $param['fixed']) {
				// we can't add a scheduled downtime to a flexible event
				$now = time();
				$start_as_seconds = nagstat::timestamp_format(nagstat::date_format(), $param['start_time']);
				$end_as_seconds = nagstat::timestamp_format(nagstat::date_format(), $param['end_time']);
				if(($start_as_seconds + $this->grace_time_in_s) < $now) {
					Database::instance()->query(
						"INSERT INTO
							report_data
							(
								timestamp,
								event_type,
								host_name,
								downtime_depth,
								output
							)
						VALUES
						(
							?,
							1103,
							?,
							1,
							?
						)",
						array(
							$start_as_seconds,
							$param['host_name'],
							"Host has entered a period of retroactively added scheduled downtime"
						)
					);
					Database::instance()->query(
						"INSERT INTO
							report_data_extras
							(
								timestamp,
								event_type,
								host_name,
								downtime_depth,
								output
							)
						VALUES
						(
							?,
							1103,
							?,
							1,
							?
						)",
						array(
							$start_as_seconds,
							$param['host_name'],
							"Host has entered a period of retroactively added scheduled downtime"
						)
					);
					if($end_as_seconds < $now) {
						Database::instance()->query(
							"INSERT INTO
								report_data
								(
									timestamp,
									event_type,
									host_name,
									downtime_depth,
									output
								)
							VALUES
							(
								?,
								1104,
								?,
								0,
								?
							)",
							array(
								$end_as_seconds,
								$param['host_name'],
								"Host has exited a period of retroactively added scheduled downtime"
							)
						);
						Database::instance()->query(
							"INSERT INTO
								report_data_extras
								(
									timestamp,
									event_type,
									host_name,
									downtime_depth,
									output
								)
							VALUES
							(
								?,
								1104,
								?,
								0,
								?
							)",
							array(
								$end_as_seconds,
								$param['host_name'],
								"Host has exited a period of retroactively added scheduled downtime"
							)
						);
					}
				}
			}
			$fallthrough = true;
			# fallthrough to services-too handling
		 case 'SCHEDULE_HOSTGROUP_HOST_DOWNTIME':
			if (!empty($param['_services-too'])) {
				unset($param['_services-too']);
				if ($fallthrough) {
					$nagios_commands = array_merge($this->_build_command('SCHEDULE_HOST_SVC_DOWNTIME', $param), $nagios_commands);
				} else {
					$nagios_commands = array_merge($this->_build_command('SCHEDULE_HOSTGROUP_SVC_DOWNTIME', $param), $nagios_commands);
				}
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
			$xcmd .= '_HOST_CHECK';
			if (!empty($param['_host-too']))
				$nagios_commands = $this->_build_command($xcmd, $param);
			break;

		 case 'ENABLE_HOST_CHECK':
		 case 'DISABLE_HOST_CHECK':
			$xcmd = $cmd{0} === 'D' ? 'DISABLE' : 'ENABLE';
			$xcmd .= '_HOST_SVC_CHECKS';
			if (!empty($param['_services-too']))
				$nagios_commands = $this->_build_command($xcmd, $param);
			break;

		 case 'ENABLE_HOST_SVC_NOTIFICATIONS':
		 case 'DISABLE_HOST_SVC_NOTIFICATIONS':
			$xcmd = $cmd{0} === 'D' ? 'DISABLE' : 'ENABLE';
			$xcmd .= '_HOST_NOTIFICATIONS';
			if (!empty($param['_host-too']))
				$nagios_commands = $this->_build_command($xcmd, $param);
			break;
		 case 'ENABLE_HOSTGROUP_SVC_CHECKS':
		 case 'DISABLE_HOSTGROUP_SVC_CHECKS':
		 case 'ENABLE_SERVICEGROUP_SVC_CHECKS':
		 case 'DISABLE_SERVICEGROUP_SVC_CHECKS':
		 case 'ENABLE_HOSTGROUP_SVC_NOTIFICATIONS':
		 case 'DISABLE_HOSTGROUP_SVC_NOTIFICATIONS':
		 case 'ENABLE_SERVICEGROUP_SVC_NOTIFICATIONS':
		 case 'DISABLE_SERVICEGROUP_SVC_NOTIFICATIONS':
		 case 'SCHEDULE_HOSTGROUP_SVC_DOWNTIME':
		 case 'SCHEDULE_SERVICEGROUP_SVC_DOWNTIME':
			if (!empty($param['_host-too'])) {
				unset($param['_host-too']);
				$xcmd = str_replace('SVC', 'HOST', $cmd);
				$nagios_commands = $this->_build_command($xcmd, $param);
			}
			break;
		 case 'SCHEDULE_SVC_DOWNTIME':
			$date_validation_result = $this->_validate_dates($param['start_time'], $param['end_time']);
			if($date_validation_result !== true) {
				$this->template->content->result = false;
				$this->template->content->error = $date_validation_result;
				return;
			}
			if(1 != $param['fixed']) {
				// we can't add a scheduled downtime to a flexible event
				break;
			}
			$now = time();
			$start_as_seconds = nagstat::timestamp_format(nagstat::date_format(), $param['start_time']);
			$end_as_seconds = nagstat::timestamp_format(nagstat::date_format(), $param['end_time']);
			list($host, $service) = explode(';', $param['service']);
			if(($start_as_seconds + $this->grace_time_in_s) < $now) {
				Database::instance()->query(
					"INSERT INTO
						report_data
						(
							timestamp,
							event_type,
							host_name,
							service_description,
							downtime_depth,
							output
						)
					VALUES
					(
						?,
						1103,
						?,
						?,
						1,
						?
					)",
					array(
						$start_as_seconds,
						$host,
						$service,
						"Service has entered a period of retroactively added scheduled downtime"
					)
				);
				Database::instance()->query(
					"INSERT INTO
						report_data_extras
						(
							timestamp,
							event_type,
							host_name,
							service_description,
							downtime_depth,
							output
						)
					VALUES
					(
						?,
						1103,
						?,
						?,
						1,
						?
					)",
					array(
						$start_as_seconds,
						$host,
						$service,
						"Service has entered a period of retroactively added scheduled downtime"
					)
				);
				if($end_as_seconds < $now) {
					Database::instance()->query(
						"INSERT INTO
							report_data
							(
								timestamp,
								event_type,
								host_name,
								service_description,
								downtime_depth,
								output
							)
						VALUES
						(
							?,
							1104,
							?,
							?,
							0,
							?
						)",
						array(
							$end_as_seconds,
							$host,
							$service,
							"Service has exited a period of retroactively added scheduled downtime"
						)
					);
					Database::instance()->query(
						"INSERT INTO
							report_data_extras
							(
								timestamp,
								event_type,
								host_name,
								service_description,
								downtime_depth,
								output
							)
						VALUES
						(
							?,
							1104,
							?,
							?,
							0,
							?
						)",
						array(
							$end_as_seconds,
							$host,
							$service,
							"Service has exited a period of retroactively added scheduled downtime"
						)
					);
				}
			}
			break;
		 case 'ACKNOWLEDGE_HOST_PROBLEM':
		 case 'REMOVE_HOST_ACKNOWLEDGEMENT':
			if (!empty($param['_services-too'])) {
				unset($param['_services-too']);
				$xcmd = str_replace('HOST', 'SVC', $cmd);
				$host = new Host_Model();
				$host_names = $param['host_name'];
				if (!is_array($host_names))
					$host_names = array($host_names);
				$xparam = $param;
				unset($xparam['host_name']);
				foreach ($host_names as $host_name) {
					foreach($host->get_services($host_name) as $service) {
						$xparam['service'] = $host_name.';'.$service->service_description;
						$nagios_commands = $this->_build_command($xcmd, $xparam, $nagios_commands);
					}
				}
			}
			break;
		}

		$nagios_commands = $this->_build_command($cmd, $param, $nagios_commands);

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
	public function unauthorized($state=false)
	{
		$this->template->content = $this->add_view('command/unauthorized');
		$this->template->content->error_message = $this->translate->_('You are not authorized to submit the specified command.');
		switch ($state) {
			case -1:  # No command passed
				$this->template->content->error_message = $this->translate->_('No command specified.');
				$this->template->content->error_description = $this->translate->_('Please enter a valid '.
					'command or use the available links in the GUI.');
				break;

			case -2:  # Contact can't submit commands
				$this->template->content->error_description = $this->translate->_("Your account doesn't seem to configured ".
					"to allow you to submit commands (i.e 'can_submit_commands' is not enabled). Please contact an administrator ".
					"to enable this for you. ");
				break;

			case -3: # not authorized from cgi.cfg, and not a configured contact
				$this->template->content->error_description = $this->translate->_("Your account doesn't seem to be ".
					"configured properly. Please contact an administrator for assistance.");
				break;

			default: # fallthrough, not authorized for anything
				$this->template->content->error_description = $this->translate->_('Read the section of the '.
					'documentation that deals with authentication and authorization in the CGIs for more information.');

		}

		$this->template->content->return_link_lable = $this->translate->_('Return from whence you came');
	}

	/**
	* Translated helptexts for this controller
	*/
	public static function _helptexts($id)
	{
		$t = zend::instance('Registry')->get('Zend_Translate');

		# No helptexts defined yet - this is just an example
		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array
			('triggered_by' =>
			 $t->_ ("With triggered downtime the start of the downtime ".
					"is triggered by the start of some other scheduled " .
					"host or service downtime"),
			 'duration' =>
			 $t->_("Duration is given as a decimal value of full hours. " .
				   "Thus, 1h 15m should be written as <b>1.25</b>"),
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
	 * Return codes:
	 *	-1:		No command passed
	 *	-2:		Contact can't submit commands
	 *	-3:		not authorized from cgi.cfg, and not a configured contact
	 * false:		fallthrough, not authorized for anything
	 */
	public function _is_authorized_for_command($params = false, $cmd = false)
	{
		$cmd = isset($params['cmd_typ']) ? $params['cmd_typ'] : $cmd;

		if (empty($cmd)) {
			return -1;
		}

		# first see if this is a contact and, if so, if that contact
		# is allowed to submit commands. If it isn't, we can bail out
		# early.
		$contact = Contact_Model::get_contact();
		if (!empty($contact)) {
			$contact = $contact->current();
			if (!$contact->can_submit_commands) {
				return -2;
			}
		}

		# second we check if this contact is allowed to submit
		# the type of command we're looking at and, if so, if
		# we can bypass fetching all the objects we're authorized
		# to see
		$auth = new Nagios_auth_Model();
		if (strstr($cmd, '_HOST_') !== false) {
			if ($auth->command_hosts_root) {
				return true;
			}
		} elseif (strstr($cmd, '_SVC_') !== false || $cmd == 'PROCESS_SERVICE_CHECK_RESULT') {
			if ($auth->command_services_root) {
				return true;
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
			return -3;

		$services = isset($params['service']) ? $params['service'] : false;
		$host_names = isset($params['host_name']) ? $params['host_name'] : false;

		# FIXME handle host/servicegroup commands as well

		# neither host_name nor service description. Either the user
		# hasn't filled out the form yet, or this regards hostgroups
		# or servicegroups
		if (!$services && !$host_names) {
			return true;
		}

		# ensure host_names is an array, as services might append more elements
		# to the array
		if (!$host_names)
			$host_names = array();
		else if (!is_array($host_names))
			$host_names = array($host_names);

		if ($services) {
			if (!is_array($services))
				$services = array($services);
			foreach ($services as $service) {
				if (strstr($service, ';')) {
					# we have host_name;service in service field
					$parts = explode(';', $service);
					if (!empty($parts) && sizeof($parts)==2) {
						$service = $parts[1];
						$host_name = $parts[0];
						$host_names[] = $parts[0];
					}
				}
				else {
					# FIXME: both host_name and service can't be an array, can they?
					$host_name = $host_names;
				}
				# if the user isn't specifically configured for the service, he/she
				# can still submit commands for it if he/she is a contact for the host
				$auth->get_authorized_services_r();
				if (isset($auth->services_r[$host_name . ';' . $service])) {
					return true;
				}
			}
		}

		if ($host_names) {
			foreach ($host_names as $host_name) {
				$auth->get_authorized_hosts_r();
				if (isset($auth->hosts_r[$host_name])) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	*	Handle submitting of one command for multiple objects
	*/
	public function multi_action()
	{
		if (!isset($_REQUEST['multi_action'])) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = '<br /> &nbsp;'.$this->translate->_('ERROR: Missing action parameter - unable to process request');
			return false;
		}

		$cmd_typ = $_REQUEST['multi_action'];
		$this->obj_type = isset($_REQUEST['obj_type']) ? $_REQUEST['obj_type'] : false;
		$this->objects = isset($_REQUEST['object_select']) ? $_REQUEST['object_select'] : false;
		if (empty($this->objects)) {
			$this->template->content = $this->add_view('error');
			$this->template->content->error_message = '<br /> &nbsp;'.$this->translate->_('ERROR: Missing objects - unable to process request');
			return false;
		}

		$param_name = false;
		switch ($this->obj_type) {
			case 'host':
				$param_name = 'host_name';
				break;
			case 'service':
				$param_name = 'service';
				break;
		}

		$params = false;

		foreach ($this->objects as $obj) {
			$params[$param_name][] = $obj;
		}

		if (!empty($params) && !empty($cmd_typ)) {
			$params['cmd_typ'] = $cmd_typ;
			return $this->submit($cmd_typ, $params);
		}

		$this->template->content = $this->add_view('error');
		$this->template->content->error_message = '<br /> &nbsp;'.$this->translate->_('ERROR: Missing parameters - unable to process request');
		return false;
	}

	/**
	*	Wrapper around nagioscmd::build_command() to be able
	*	to create valid commands for multiple objects at once
	*/
	public function _build_command($cmd = false, $param = false, $nagios_commands = false)
	{
		if (
		(isset($param['host_name']) && is_array($param['host_name'])) ||
		(isset($param['service']) && is_array($param['service'])) )
		{ # we have a multi command, i.e one command for multiple objects

			# remove host_name (or service) from param
			if (isset($param['host_name'])) {
				$obj_list = $param['host_name'];
				unset($param['host_name']);
				$param_str = 'host_name';
			} else {
				$obj_list = $param['service'];
				unset($param['service']);
				$param_str = 'service';
			}

			# create new param array for each object
			foreach ($obj_list as $obj) {
				$multi_param = false;
				$multi_param = $param;
				$multi_param[$param_str] = $obj;
				$nagios_commands[] = nagioscmd::build_command($cmd, $multi_param);
			}
		} else if ((isset($param['downtime_id']) && is_array($param['downtime_id'])) ||
			(isset($param['trigger_id']) && is_array($param['trigger_id']))) {
				if (isset($param['trigger_id']))
					$param_str = 'trigger_id';
				else
					$param_str = 'downtime_id';
			foreach ($param[$param_str] as $did) {
				$multi_param = $param;
				$multi_param[$param_str] = $did;
				$nagios_commands[] = nagioscmd::build_command($cmd, $multi_param);
			}
		} else {
			$nagios_commands[] = nagioscmd::build_command($cmd, $param);
		}

		return $nagios_commands;
	}
}
