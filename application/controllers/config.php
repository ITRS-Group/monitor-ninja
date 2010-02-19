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

		$search = array('d','u','r','f','d');
		$replace = array($t->_('Down'), $t->_('Unreachable'), $t->_('Recovery'), $t->_('Flapping'), $t->_('Downtime'));

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
					//$t->_('Retention Options')
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$result[$i][]= $row->host_name;
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
					$result[$i][]= $row->notification_options; // Down, Unreachable, Recovery, Flapping, Downtime
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->notification_period, $row->notification_period);
					$result[$i][]= $row->event_handler == 0 ? '&nbsp;' : $row->event_handler;
					$result[$i][]= $row->event_handler_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->stalking_options == 'n' ? $t->_('None') : $t->_('??');
					$result[$i][]= $row->flap_detection_enabled == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->low_flap_threshold == 0.0 ? $t->_('Program-wide value') : $row->low_flap_threshold;
					$result[$i][]= $row->high_flap_threshold == 0.0 ? $t->_('Program-wide value') : $row->high_flap_threshold;
					$result[$i][]= $row->process_perf_data == 1 ? $t->_('Yes') : $t->_('No');
					$result[$i][]= $row->failure_prediction_enabled == 1 ? $t->_('Yes') : $t->_('No');
					//
					$result[$i][]= $row->notes;
					$result[$i][]= $row->notes_url;
					$result[$i][]= $row->action_url;
					$result[$i][]= $row->icon_image;
					$result[$i][]= $row->icon_image_alt;
					//
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
					//$t->_('Retention Options'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$note_options = explode(',',$row->notification_options);

					$result[$i][]= $row->host_name;
					$result[$i][]= $row->service_description;
					$result[$i][]= $row->max_check_attempts;
					$result[$i][]= time::to_string($row->check_interval);
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
					$result[$i][]= $row->notification_options; // d,u,r,f,d -> Down, Unreachable, Recovery, Flapping, Downtime
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
					// $t->_('Retention Options'),
				);
				$data = $config_model->list_config($this->type);
				$i = 0;
				foreach($data as $row) {
					$result[$i][]= $row->contact_name;
					$result[$i][]= $row->alias;
					$result[$i][]= html::anchor('mailto:'.$row->email, $row->email);
					$result[$i][]= $row->pager;
					$result[$i][]= $row->service_notification_options; // Unknown, Warning, Critical, Recovery, Flapping
					$result[$i][]= $row->host_notification_options; // Down, Unreachable, Recovery, Flapping
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->service_notification_period, $row->service_notification_period == 0 ? $t->_('None') : $row->service_notification_period);
					$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->host_notification_period, $row->host_notification_period == 0 ? $t->_('None') : $row->host_notification_period);
					$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->service_notification_commands, $row->service_notification_commands);
					$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->host_notification_commands, $row->host_notification_commands);
					// retention options
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
					$result[$i][]= $row->contactgroup_name;
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
					$result[$i][]= $row->hostgroup_name;
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
					$t->_('Dependency Failure Options'),
				);
			break;

			case 'host_escalations': // ******************************************************************
				$header = array(
					$t->_('Host'),
					$t->_('Contacts/Groups'), //?
					$t->_('First Notification'),
					$t->_('Last Notification'),
					$t->_('Notification Interval'),
					$t->_('Escalation Period'),
					$t->_('Escalation Options'),
				);
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
						$result[$i][]= $row->servicegroup_name;
						$result[$i][]= $row->alias;

						$travel = $sgm->get_services_for_group($row->servicegroup_name);
						if (count($travel) > 0) {
							$temp = false;
							foreach ($travel as $trip) {
								$temp[] = html::anchor(Router::$controller.'/?type=services#'.$trip->description, $trip->description);
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
					$t->_('Dependency Failure Options'),
				);
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
			break;
		}
		//die (Kohana::debug($data));
		$this->template->content->header = $header;
		$this->template->content->data = $data;
		$this->template->content->type = $this->type;
	}
}