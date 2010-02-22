	<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Config controller
 * Requires authentication
 *
 * @package	NINJA
 * @author	op5 AB
 * @license	GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Config_Controller extends Authenticated_Controller {
	public $current = false;
	public $logos_path = '';
	public $type = 'hosts';

	public function __construct()
	{
		parent::__construct();

		$this->logos_path = Kohana::config('config.logos_path');
	}

	/**
	 * Default controller method
	 * Redirects to show_process_info() which
	 * is the equivalent of calling extinfo.cgi?type=0
	 */
	public function index()
	{

		$this->type = isset($_GET['type']) ? $_GET['type'] : $this->type;
		$t = $this->translate;

		$items_per_page = 20;
		$config_model = new Config_Model($items_per_page, true, true);

		$this->template->title = $this->translate->_('Configuration').' » '.$this->translate->_('View config');
		$this->template->content = $this->add_view('config/index');
		$data = $config_model->list_config($this->type);

		$options['host'] = array(
			'notification' => array(
				'd' => $t->_('Down'),
				'u' => $t->_('Unreachable'),
				'r' => $t->_('Recovery'),
				'f' => $t->_('Flapping start and stop'),
				's' => $t->_('Scheduled downtime start and stop'),
				'n' => false
			),
			'escalation' => array(
				'd' => $t->_('Down'),
				'u' => $t->_('Unreachable'),
				'r' => $t->_('Recovery'),
				'n' => false
			),
			'notification_failure' => array(
				'd' => $t->_('Down'),
				'u' => $t->_('Unreachable'),
				'o' => $t->_('OK'),
				'n' => false
			),
			'execution_failure' => array(
				'd' => $t->_('Down'),
				'u' => $t->_('Unreachable'),
				'o' => $t->_('OK'),
				'n' => false
			),
		);

		$options['service'] = array(
			'notification' => array(
				'c' => $t->_('Critical'),
				'w' => $t->_('Warning'),
				'u' => $t->_('Unknown'),
				'r' => $t->_('Recovery'),
				'f' => $t->_('Flapping start and stop'),
				's' => $t->_('Scheduled downtime start and stop'),
				'n' => false
			),
			'escalation' => array(
				'c' => $t->_('Critical'),
				'w' => $t->_('Warning'),
				'u' => $t->_('Unknown'),
				'r' => $t->_('Recovery'),
				'n' => false
			),
			'notification_failure' => array(
				'c' => $t->_('Critical'),
				'w' => $t->_('Warning'),
				'u' => $t->_('Unknown'),
				'o' => $t->_('OK'),
				'n' => false
			),
			'execution_failure' => array(
				'c' => $t->_('Critical'),
				'w' => $t->_('Warning'),
				'u' => $t->_('Unknown'),
				'o' => $t->_('OK'),
				'n' => false
			),
		);

		switch ($this->type) {
			case 'hosts': // *****************************************************************************
				$header = array(
					$t->_('Host Name'),
					$t->_('Alias/Description'),
					$t->_('Address'),
					$t->_('Parent Hosts'),
					$t->_('Max. Check Attempts'),
					$t->_('Check Interval'),
					$t->_('Retry Interval'),
					$t->_('Host Check Command'),
					$t->_('Check Period'),
					$t->_('Obsess Over'),
					$t->_('Enable Active Checks'),
					$t->_('Enable Passive Checks'),
					$t->_('Check Freshness'),
					$t->_('Freshness Threshold'),
					$t->_('Default Contact Groups'),
					$t->_('Notification Interval'),
					$t->_('First Notification Delay'),
					$t->_('Notification Options'),
					$t->_('Notification Period'),
					$t->_('Event Handler'),
					$t->_('Enable Event Handler'),
					$t->_('Stalking Options'),
					$t->_('Enable Flap Detection'),
					$t->_('Low Flap Threshold'),
					$t->_('High Flap Threshold'),
					$t->_('Process Performance Data'),
					$t->_('Enable Failure Prediction'),
					//$t->_('Failure Prediction Options'),
					$t->_('Notes'),
					$t->_('Notes URL'),
					$t->_('Action URL'),
					$t->_('Icon image'),
					$t->_('Icon image alt'),
					$t->_('Retention Options')
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$result[$i][]= '<a name="'.$row->host_name.'"></a>'.$row->host_name;
					$result[$i][]= $row->alias;
					$result[$i][]= $row->address;
					$result[$i][]= html::anchor(Router::$controller.'/?type=hosts#'.$row->parent, $row->parent);
					$result[$i][]= $row->max_check_attempts;
					$result[$i][]= time::to_string($row->check_interval*60);
					$result[$i][]= time::to_string($row->retry_interval);
					$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->check_command, $row->check_command);
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->check_period, $row->check_period);
					$result[$i][]= $row->obsess_over_host == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->active_checks_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->passive_checks_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->check_freshness == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->freshness_threshold == 0 ? $t->_('Auto-determined value') : $row->freshness_threshold.' '.$t->_('seconds');
					$result[$i][]= html::anchor(Router::$controller.'/?type=contact_groups#'.$row->contactgroup_name, $row->contactgroup_name);
					$result[$i][]= $row->notification_interval == 0 ? $t->_('No Re-notification') : $row->notification_interval;
					$result[$i][]= time::to_string($row->first_notification_delay);
						$note_options = explode(',',$row->notification_options);
						$tmp = false;
						foreach($note_options as $option) {
							$tmp[] = $options['host']['notification'][$option];
						}
					$result[$i][]= implode(', ',$tmp);
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->notification_period, $row->notification_period);
					$result[$i][]= $row->event_handler == 0 ? '&nbsp;' : $row->event_handler;
					$result[$i][]= $row->event_handler_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->stalking_options == 'n' ? $t->_('None') : $t->_('??');
					$result[$i][]= $row->flap_detection_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->low_flap_threshold == 0.0 ? $t->_('Program-wide value') : $row->low_flap_threshold;
					$result[$i][]= $row->high_flap_threshold == 0.0 ? $t->_('Program-wide value') : $row->high_flap_threshold;
					$result[$i][]= $row->process_perf_data == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->failure_prediction_enabled == 1 ? $t->_('Yes') : $t->_('No');
					// failure predition options
					$result[$i][]= $row->notes;
					$result[$i][]= $row->notes_url;
					$result[$i][]= $row->action_url;
					$result[$i][]= $row->icon_image;
					$result[$i][]= $row->icon_image_alt;
					// retention options
					$ret = false;
					if ($row->retain_status_information == true) {
						$ret[] = $t->_('Status Information');
						$retention = 1;
					}
					if ($row->retain_nonstatus_information == true) {
						$ret[] = $t->_('Non-status Information');
					}
					if ($retention != 1) {
						$ret[] = $t->_('None');
					}
					$result[$i][] = implode(', ',$ret);
					$i++;
				}
				$data = $result;
			break;

			case 'services': // **************************************************************************
				$header = array(
					$t->_('Host'),
					$t->_('Service Description'),
					$t->_('Max. Check Attempts'),
					$t->_('Normal Check Interval'),
					$t->_('Retry Check Interal'),
					$t->_('Check Command'),
					$t->_('Check Period'),
					$t->_('Parallelize'),
					$t->_('Volatile'),
					$t->_('Obsess Over'),
					$t->_('Enable Active Checks'),
					$t->_('Enable Passive Checks'),
					$t->_('Check Freshness'),
					$t->_('Freshness Threshold'),
					$t->_('Default Contact Groups'),
					$t->_('Enable Notifications'),
					$t->_('Notification Interval'),
					$t->_('Notification Options'),
					$t->_('Notification Period'),
					$t->_('Event Handler'),
					$t->_('Enable Event Handler'),
					$t->_('Stalking Options'),
					$t->_('Enable Flap Detection'),
					$t->_('Low Flap Threshold'),
					$t->_('High Flap Threshold'),
					$t->_('Process Performance Data'),
					$t->_('Enable Failure Prediction'),
					//$t->_('Failure Prediction Options'),
					$t->_('Notes'),
					$t->_('Notes URL'),
					$t->_('Action URL'),
					$t->_('Icon image'),
					$t->_('Icon image alt'),
					$t->_('Retention Options'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$note_options = explode(',',$row->notification_options);

					$result[$i][]= '<a name="'.$row->host_name.'"></a>'.$row->host_name;
					$result[$i][]= '<a name="'.$row->service_description.'"></a>'.$row->service_description;
					$result[$i][]= $row->max_check_attempts;
					$result[$i][]= time::to_string($row->check_interval*60);
					$result[$i][]= time::to_string($row->retry_interval);
					$result[$i][]= $row->check_command;
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->check_period, $row->check_period);
					$result[$i][]= $row->parallelize_check == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->is_volatile == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->obsess_over_service == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->active_checks_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->passive_checks_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->check_freshness == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->freshness_threshold == 0 ? $t->_('Auto-determined value') : $row->freshness_threshold.' '.$t->_('seconds');
					$result[$i][]= html::anchor(Router::$controller.'/?type=contacts#'.$row->contactgroup_name, $row->contactgroup_name);
					$result[$i][]= $row->notifications_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->notification_interval == 0 ? $t->_('No Re-notification') : $row->notification_interval;
					$notification_options = explode(',',$row->notification_options);
						$tmp = false;
						foreach($notification_options as $option) {
							$tmp[] = $options['service']['notification'][$option];
						}
					$result[$i][]= implode(', ',$tmp);
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->notification_period, $row->notification_period);
					$result[$i][]= $row->event_handler == 0 ? '&nbsp;' : $row->event_handler;
					$result[$i][]= $row->event_handler_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->stalking_options == 'n' ? $t->_('None') : $t->_('??');
					$result[$i][]= $row->flap_detection_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->low_flap_threshold == 0.0 ? $t->_('Program-wide value') : $row->low_flap_threshold;
					$result[$i][]= $row->high_flap_threshold == 0.0 ? $t->_('Program-wide value') : $row->high_flap_threshold;
					$result[$i][]= $row->process_perf_data == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->failure_prediction_enabled == 1 ? $t->_('Yes') : $t->_('No');
					// failure prediction options
					$result[$i][]= $row->notes;
					$result[$i][]= $row->notes_url;
					$result[$i][]= $row->action_url;
					$result[$i][]= $row->icon_image;
					$result[$i][]= $row->icon_image_alt;
					//retention options
					$ret = false;
					if ($row->retain_status_information == true) {
						$ret[] = $t->_('Status Information');
						$retention = 1;
					}
					if ($row->retain_nonstatus_information == true) {
						$ret[] = $t->_('Non-status Information');
					}
					if ($retention != 1) {
						$ret[] = $t->_('None');
					}
					$result[$i][] = implode(', ',$ret);
					$i++;
				}
				$data = $result;
			break;

			case 'contacts': // **************************************************************************
				$header = array(
					$t->_('Contact Name'),
					$t->_('Alias'),
					$t->_('Email Address'),
					$t->_('Pager Address/Number'),
					$t->_('Service Notification Options'),
					$t->_('Host Notification Options'),
					$t->_('Service Notification Period'),
					$t->_('Host Notification Period'),
					$t->_('Service Notification Commands'),
					$t->_('Host Notification Commands'),
					$t->_('Retention Options'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$result[$i][]= '<a name="'.$row->contact_name.'"></a>'.$row->contact_name;
					$result[$i][]= $row->alias;
					$result[$i][]= html::anchor('mailto:'.$row->email, $row->email);
					$result[$i][]= $row->pager;

					$s_notification_options = explode(',',$row->service_notification_options);
						$s_tmp = false;
						foreach($s_notification_options as $s_option) {
							$s_tmp[] = $options['service']['notification'][$s_option];
						}
					$result[$i][]= implode(', ',$s_tmp);

					$h_notification_options = explode(',',$row->host_notification_options);
						$h_tmp = false;
						foreach($h_notification_options as $h_option) {
							$h_tmp[] = $options['host']['notification'][$h_option];
						}
					$result[$i][]= implode(', ',$h_tmp);

					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->service_notification_period, $row->service_notification_period == 0 ? $t->_('None') : $row->service_notification_period);
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->host_notification_period, $row->host_notification_period == 0 ? $t->_('None') : $row->host_notification_period);
					$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->service_notification_commands, $row->service_notification_commands);
					$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->host_notification_commands, $row->host_notification_commands);
					// retention options
					$ret = false;
					if ($row->retain_status_information == true) {
						$ret[] = $t->_('Status Information');
						$retention = 1;
					}
					if ($row->retain_nonstatus_information == true) {
						$ret[] = $t->_('Non-status Information');
					}
					if ($retention != 1) {
						$ret[] = $t->_('None');
					}
					$result[$i][] = implode(', ',$ret);
					$i++;
				}
				$data = $result;
			break;

			case 'contact_groups': // ********************************************************************
				$header = array(
					$t->_('Group Name'),
					$t->_('Description'),
					$t->_('Contact Members'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$result[$i][]= '<a name="'.$row->contactgroup_name.'"></a>'.$row->contactgroup_name;
					$result[$i][]= $row->alias;

					$travel = Contactgroup_Model::get_members($row->contactgroup_name);
					if (count($travel) > 0) {
						$temp = false;
						foreach ($travel as $trip) {
							$temp[] = html::anchor(Router::$controller.'/?type=contacts#'.$trip->contact_name, $trip->contact_name);
						}
						$result[$i][]= implode(', ',$temp);
					}
					else
						$result[$i][]= '';
					$i++;
				}
				$data = $result;
			break;

			case 'timeperiods': // ***********************************************************************
				$header = array(
					$t->_('Name'),
					$t->_('Alias/Description'),
					$t->_('Sunday Time Ranges'),
					$t->_('Monday Time Ranges'),
					$t->_('Tuesday Time Ranges'),
					$t->_('Wednesday Time Ranges'),
					$t->_('Thursday Time Ranges'),
					$t->_('Friday Time Ranges'),
					$t->_('Saturday Time Ranges'),
				);
			break;

			case 'commands': // **************************************************************************
				$header = array(
					$t->_('Command Name'),
					$t->_('Command Line')
				);
			break;

			case 'host_groups': // ***********************************************************************
				$header = array(
					$t->_('Group Name'),
					$t->_('Description'),
					$t->_('Host Members'),
					$t->_('Notes'),
					$t->_('Notes URL'),
					$t->_('Action URL'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				$hgm = new Hostgroup_Model;
				foreach($data as $row) {
					$result[$i][]= '<a name="'.$row->hostgroup_name.'"></a>'.$row->hostgroup_name;
					$result[$i][]= $row->alias;

					$travel = $hgm->get_hosts_for_group($row->hostgroup_name);
					if (count($travel) > 0) {
						$temp = false;
						foreach ($travel as $trip) {
							$temp[] = html::anchor(Router::$controller.'/?type=hosts#'.$trip->host_name, $trip->host_name);
						}
						$result[$i][]= implode(', ',$temp);
					}
					else
						$result[$i][]= '';

					$result[$i][]= $row->notes;
					$result[$i][]= $row->notes_url;
					$result[$i][]= $row->action_url;
					$i++;
				}
				$data = $result;
			break;
			case 'host_dependencies': // *****************************************************************
				$header = array(
					$t->_('Dependent Host'),
					$t->_('Master Host'),
					$t->_('Dependency Type'),
					$t->_('Dependency Period'),
					$t->_('Dependency Failure Options'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					if ($row->execution_failure_options != NULL) {
						$result[$i][]= html::anchor(Router::$controller.'/?type=hosts#'.$row->dependent, $row->dependent);
						$result[$i][]= html::anchor(Router::$controller.'/?type=hosts#'.$row->master, $row->master);
						$result[$i][]= $t->_('Check Execution');
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->dependency_period, $row->dependency_period);
						$execution_failure_options = explode(',',$row->execution_failure_options);
						$tmp = false;
						foreach($execution_failure_options as $option) {
							$tmp[] = $options['host']['execution_failure'][$option];
						}
						$result[$i][]= implode(', ',$tmp);
						$i++;
					}
					if ($row->notification_failure_options != NULL) {
						$result[$i][]= html::anchor(Router::$controller.'/?type=hosts#'.$row->dependent, $row->dependent);
						$result[$i][]= html::anchor(Router::$controller.'/?type=hosts#'.$row->master, $row->master);
						$result[$i][]= $t->_('Notification');
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->dependency_period, $row->dependency_period);
						$notification_failure_options = explode(',',$row->notification_failure_options);
						$n_tmp = false;
						foreach($notification_failure_options as $n_option) {
							$n_tmp[] = $options['host']['notification_failure'][$n_option];
						}
						$result[$i][]= implode(', ',$n_tmp);
						$i++;
					}
				}
				$data = $result;
			break;

			case 'host_escalations': // ******************************************************************
				$header = array(
					$t->_('Host'),
					$t->_('Contacts/Groups'),
					$t->_('First Notification'),
					$t->_('Last Notification'),
					$t->_('Notification Interval'),
					$t->_('Escalation Period'),
					$t->_('Escalation Options'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$result[$i][]= html::anchor(Router::$controller.'/?type=hosts#'.$row->host_name, $row->host_name);
					$result[$i][]= html::anchor(Router::$controller.'/?type=contactgroup#'.$row->contactgroup_name, $row->contactgroup_name);
					$result[$i][]= $row->first_notification;
					$result[$i][]= $row->last_notification;
					$result[$i][]= time::to_string($row->notification_interval*60);
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->escalation_period, $row->escalation_period);

					$escalation_options = explode(',',$row->escalation_options);
						$tmp = false;
						foreach($escalation_options as $option) {
							$tmp[] = $options['host']['escalation'][$option];
						}
					$result[$i][]= implode(', ',$tmp);

					$i++;
				}
				$data = $result;
			break;

			case 'service_groups': // ********************************************************************
				$header = array(
					$t->_('Group Name'),
					$t->_('Description'),
					$t->_('Service Members'),
					$t->_('Notes'),
					$t->_('Notes URL'),
					$t->_('Action URL'),
				);
				$data = $config_model->list_config($this->type);
				if ($data!==false) {
					$i = 0;
					$sgm = new Servicegroup_Model;
					foreach($data as $row) {
						$result[$i][]= '<a name="'.$row->servicegroup_name.'"></a>'.$row->servicegroup_name;
						$result[$i][]= $row->alias;

						$travel = $sgm->get_services_for_group($row->servicegroup_name);
						if (count($travel) > 0) {
							$temp = false;
							foreach ($travel as $trip) {
								$temp[] = html::anchor(Router::$controller.'/?type=hosts#'.$trip->host_name, $trip->host_name).' / '.
													html::anchor(Router::$controller.'/?type=services#'.$trip->service_description, $trip->service_description);
							}
							$result[$i][]= implode(', ',$temp);
						}
						else
							$result[$i][]= '';

						$result[$i][]= $row->notes;
						$result[$i][]= $row->notes_url;
						$result[$i][]= $row->action_url;
						$i++;
					}
					$data = $result;
				}
			break;

			case 'service_dependencies': // **************************************************************
				$header = array(
					$t->_('Host (dependent)'),
					$t->_('Service (dependent)'),
					$t->_('Host (master)'),
					$t->_('Service (master)'),
					$t->_('Dependency Type'),
					$t->_('Dependency Period'),
					$t->_('Dependency Failure Options'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					if ($row->execution_failure_options != NULL) {
						$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->dependent_host, $row->dependent_host);
						$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->dependent_service, $row->dependent_service);
						$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->master_host, $row->master_host);
						$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->master_service, $row->master_service);
						$result[$i][]= $t->_('Check Execution');
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->dependency_period, $row->dependency_period);
						$execution_failure_options = explode(',',$row->execution_failure_options);
						$tmp = false;
						foreach($execution_failure_options as $option) {
							$tmp[] = $options['service']['execution_failure'][$option];
						}
						$result[$i][]= implode(', ',$tmp);
						$i++;
					}
					if ($row->notification_failure_options != NULL) {
						$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->dependent_host, $row->dependent_host);
						$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->dependent_service, $row->dependent_service);
						$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->master_host, $row->master_host);
						$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->master_service, $row->master_service);
						$result[$i][]= $t->_('Notification');
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->dependency_period, $row->dependency_period);
						//$result[$i][]= $row->notification_failure_options;
						$notification_failure_options = explode(',',$row->notification_failure_options);
						$n_tmp = false;
						foreach($notification_failure_options as $n_option) {
							$n_tmp[] = $options['service']['notification_failure'][$n_option];
						}
						$result[$i][]= implode(', ',$n_tmp);
						$i++;
					}
				}
				$data = $result;
			break;

			case 'service_escalations': // ***************************************************************
				$header = array(
					$t->_('Host'),
					$t->_('Service description'),
					$t->_('Contacts/Groups'),
					$t->_('First Notification'),
					$t->_('Last Notification'),
					$t->_('Notification Interval'),
					$t->_('Escalation Period'),
					$t->_('Escalation Options'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$result[$i][]= html::anchor(Router::$controller.'/?type=hosts#'.$row->host_name, $row->host_name);
					$result[$i][]= html::anchor(Router::$controller.'/?type=services#'.$row->service_description, $row->service_description);
					$result[$i][]= html::anchor(Router::$controller.'/?type=contactgroup#'.$row->contactgroup_name, $row->contactgroup_name);
					$result[$i][]= $row->first_notification;
					$result[$i][]= $row->last_notification;
					$result[$i][]= time::to_string($row->notification_interval*60);
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->escalation_period, $row->escalation_period);

					$escalation_options = explode(',',$row->escalation_options);
						$tmp = false;
						foreach($escalation_options as $option) {
							$tmp[] = $options['service']['escalation'][$option];
						}
					$result[$i][]= implode(', ',$tmp);
					$i++;
				}
				$data = $result;
			break;
		}
		//die (Kohana::debug($data));
		$this->xtra_js[] = 'application/media/js/jquery.tablesorter.min.js';
		$this->xtra_js[] = $this->add_path('config/js/config.js');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;
		$this->template->content->header = $header;
		$this->template->content->data = $data;
		$this->template->content->type = $this->type;
	}
}