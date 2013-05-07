<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Config controller
 * Requires authentication
 *
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
	public $type = 'hosts';
	const SERVICE_NOTIFICATION_COMMANDS =  'service_notification_commands';
	const HOST_NOTIFICATION_COMMANDS = 'host_notification_commands';

	/**
	 * Default controller method
	 * Redirects to show_process_info() which
	 * is the equivalent of calling extinfo.cgi?type=0
	 */
	public function index()
	{
		if (!Auth::instance()->authorized_for('system_information')) {
			return url::redirect('extinfo/unauthorized/0');
		}

		$this->type = isset($_GET['type']) ? $_GET['type'] : $this->type;
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.default.items_per_page', '*'));
		$pagination = new CountlessPagination(array('items_per_page' => $items_per_page));

		$config_model = new Config_Model();
		$config_model->set_range(
			$pagination->items_per_page,
			($pagination->current_page-1)*$pagination->items_per_page
		);

		$filter = $this->input->get('filterbox', null);
		if($filter && $filter == _('Enter text to filter')) {
			$filter = null;
		}
		$data = $config_model->list_config($this->type, $filter);
		$result = array();
		$this->template->title = _('Configuration').' » '._('View config');
		$this->template->content = $this->add_view('config/index');
		$this->template->content->pagination = $pagination;

		switch ($this->type) {
			case 'hosts': // *****************************************************************************
				$header = array(
					_('Host Name'),
					_('Alias/Description'),
					_('Display Name'),
					_('Address'),
					_('Parent Hosts'),
					_('Max. Check Attempts'),
					_('Check Interval'),
					_('Retry Interval'),
					_('Host Check Command'),
					_('Check Period'),
					_('Obsess Over'),
					_('Enable Active Checks'),
					_('Enable Passive Checks'),
					_('Check Freshness'),
					//_('Freshness Threshold'),
					_('Default Contact Groups'),
					_('Notification Interval'),
					_('First Notification Delay'),
					//_('Notification Options'),
					_('Notification Period'),
					_('Event Handler'),
					_('Enable Event Handler'),
					//_('Stalking Options'),
					_('Enable Flap Detection'),
					_('Low Flap Threshold'),
					_('High Flap Threshold'),
					_('Process Performance Data'),
					//_('Enable Failure Prediction'),
					//_('Failure Prediction Options'),
					_('Notes'),
					_('Notes URL'),
					_('Action URL'),
					_('Icon image'),
					_('Icon image alt'),
					//_('Retention Options')
				);

				if ($data!==false) {
					$i = 0;
					$result=array();
					foreach($data as $row) {
						$row = (object) $row;
						$result[$i][]= '<a name="'.$row->name.'"></a>'.$row->name;
						$result[$i][]= $row->alias;
						$result[$i][]= $row->display_name;
						$result[$i][]= $row->address;
						$tmp = array();
						foreach ($row->parents as $parent) {
							$tmp[] = html::anchor(Router::$controller.'/?type=hosts#'.$parent, $parent);
						}
						$result[$i][] = implode(', ',$tmp);
						$result[$i][]= $row->max_check_attempts;
						$result[$i][]= time::to_string($row->check_interval*60);
						$result[$i][]= time::to_string($row->retry_interval*60);
						$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->check_command, $row->check_command);
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->check_period, $row->check_period);
						$result[$i][]= $row->obsess == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->active_checks_enabled == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->accept_passive_checks == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->check_freshness == 1 ? _('Yes') : _('No');
						//$result[$i][]= $row->freshness_threshold == 0 ? _('Auto-determined value') : $row->freshness_threshold.' '._('seconds');
						$c_link = array();
						foreach($row->contact_groups as $cg){
							$c_link[] = html::anchor(Router::$controller.'/?type=contact_groups#'.$cg, $cg);
						}
						foreach($row->contacts as $c){
							$c_link[] = html::anchor(Router::$controller.'/?type=contacts#'.$c, $c);
						}
						$result[$i][] = implode(', ', $c_link);

						$result[$i][]= $row->notification_interval == 0 ? _('No Re-notification') : $row->notification_interval;
						$result[$i][]= time::to_string($row->first_notification_delay);
						//$note_options = explode(',',$row->notification_options);
						//$tmp = false;
						//foreach($note_options as $option) {
							//$tmp[] = $options['host']['notification'][$option];
						//}
						//$result[$i][]= implode(', ',$tmp);
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->notification_period, $row->notification_period);
						$result[$i][]= $row->event_handler == 0 ? '&nbsp;' : $row->event_handler;
						$result[$i][]= $row->event_handler_enabled == 1 ? _('Yes') : _('No');
						//$result[$i][]= $row->stalking_options == 'n' ? _('None') : _('??');
						$result[$i][]= $row->flap_detection_enabled == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->low_flap_threshold == 0.0 ? _('Program-wide value') : $row->low_flap_threshold;
						$result[$i][]= $row->high_flap_threshold == 0.0 ? _('Program-wide value') : $row->high_flap_threshold;
						$result[$i][]= $row->process_performance_data == 1 ? _('Yes') : _('No');
						//$result[$i][]= $row->failure_prediction_enabled == 1 ? _('Yes') : _('No');
						//$result[$i][]= !isset($row->failure_prediction_options) ? '&nbsp;' : $row->failure_prediction_options; // ?
						$result[$i][]= $row->notes;
						$result[$i][]= $row->notes_url;
						$result[$i][]= $row->action_url;
						$result[$i][]= $row->icon_image;
						$result[$i][]= $row->icon_image_alt;
						// retention options
						//$ret = false;
						//if ($row->retain_status_information == true) {
							//$ret[] = _('Status Information');
						//}
						//if ($row->retain_nonstatus_information == true) {
							//$ret[] = _('Non-status Information');
						//}
						//$result[$i][] = is_array($ret) ? implode(', ',$ret) : 'None';
						$i++;
					}
					$data = $result;
				}
				break;

			case 'services': // **************************************************************************
				$header = array(
					_('Host'),
					_('Service Description'),
					_('Max. Check Attempts'),
					_('Normal Check Interval'),
					_('Retry Check Interval'),
					_('Check Command'),
					_('Check Period'),
					//_('Parallelize'),
					//_('Volatile'),
					_('Obsess Over'),
					_('Enable Active Checks'),
					_('Enable Passive Checks'),
					_('Check Freshness'),
					//_('Freshness Threshold'),
					_('Default Contact Groups'),
					_('Enable Notifications'),
					_('Notification Interval'),
					//_('Notification Options'),
					_('Notification Period'),
					_('Event Handler'),
					_('Enable Event Handler'),
					//_('Stalking Options'),
					_('Enable Flap Detection'),
					_('Low Flap Threshold'),
					_('High Flap Threshold'),
					_('Process Performance Data'),
					//_('Enable Failure Prediction'),
					//_('Failure Prediction Options'),
					_('Notes'),
					_('Notes URL'),
					_('Action URL'),
					_('Icon image'),
					_('Icon image alt'),
					//_('Retention Options'),
				);


				if ($data!==false) {
					$i = 0;
					foreach($data as $row) {
						$row = (object) $row;
						//$note_options = explode(',',$row->notification_options);

						$result[$i][]= '<a name="'.$row->host_name.'"></a>'.$row->host_name;
						$result[$i][]= '<a name="'.$row->description.'"></a>'.$row->description;
						$result[$i][]= $row->max_check_attempts;
						$result[$i][]= time::to_string($row->check_interval*60);
						$result[$i][]= time::to_string($row->retry_interval*60);
						$result[$i][]= $row->check_command;
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->check_period, $row->check_period);
						//$result[$i][]= $row->parallelize_check == 1 ? _('Yes') : _('No');
						//$result[$i][]= $row->is_volatile == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->obsess == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->active_checks_enabled == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->accept_passive_checks == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->check_freshness == 1 ? _('Yes') : _('No');
						//$result[$i][]= $row->freshness_threshold == 0 ? _('Auto-determined value') : $row->freshness_threshold.' '._('seconds');

						$c_link = array();
						foreach($row->contact_groups as $cg){
							$c_link[] = html::anchor(Router::$controller.'/?type=contact_groups#'.$cg, $cg);
						}
						foreach($row->contacts as $c){
							$c_link[] = html::anchor(Router::$controller.'/?type=contacts#'.$c, $c);
						}
						$result[$i][] = implode(', ', $c_link);

						$result[$i][]= $row->notifications_enabled == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->notification_interval == 0 ? _('No Re-notification') : $row->notification_interval;
						//$notification_options = explode(',',$row->notification_options);
						//$tmp = array();
						//foreach($notification_options as $option) {
							//$tmp[] = $options['service']['notification'][$option];
						//}
						//$result[$i][]= is_array($tmp) ? implode(', ',$tmp) : '';
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->notification_period, $row->notification_period);
						$result[$i][]= $row->event_handler == 0 ? '&nbsp;' : $row->event_handler;
						$result[$i][]= $row->event_handler_enabled == 1 ? _('Yes') : _('No');
						//$result[$i][]= $row->stalking_options == 'n' ? _('None') : _('??');
						$result[$i][]= $row->flap_detection_enabled == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->low_flap_threshold == 0.0 ? _('Program-wide value') : $row->low_flap_threshold;
						$result[$i][]= $row->high_flap_threshold == 0.0 ? _('Program-wide value') : $row->high_flap_threshold;
						$result[$i][]= $row->process_performance_data == 1 ? _('Yes') : _('No');
						//$result[$i][]= $row->failure_prediction_enabled == 1 ? _('Yes') : _('No');
						//$result[$i][]= !isset($row->failure_prediction_options) ? '&nbsp;' : $row->failure_prediction_options; // ?
						$result[$i][]= $row->notes;
						$result[$i][]= $row->notes_url;
						$result[$i][]= $row->action_url;
						$result[$i][]= $row->icon_image;
						$result[$i][]= $row->icon_image_alt;
						//retention options
						//$ret = false;
						//if ($row->retain_status_information == true) {
							//$ret[] = _('Status Information');
						//}
						//if ($row->retain_nonstatus_information == true) {
							//$ret[] = _('Non-status Information');
						//}
						//$result[$i][] = is_array($ret) ? implode(', ',$ret) : 'None';
						$i++;
					}
					$data = $result;
				}
			break;

			case 'contacts': // **************************************************************************
				$header = array(
					_('Contact Name'),
					_('Alias'),
					_('Email Address'),
					_('Pager Address/Number'),
					//_('Service Notification Options'),
					//_('Host Notification Options'),
					_('Service Notification Period'),
					_('Host Notification Period'),
					//_('Service Notification Commands'),
					//_('Host Notification Commands'),
					//_('Retention Options'),
				);

				if ($data!==false) {
					$i = 0;
					foreach($data as $row) {
						$row = (object) $row;
						$result[$i][]= '<a name="'.$row->name.'"></a>'.$row->name;
						$result[$i][]= $row->alias;
						$result[$i][]= '<a href="mailto:'.$row->email.'">'.$row->email.'</a>';
						$result[$i][]= $row->pager;

						//$s_notification_options = explode(',',$row->service_notification_options);
						//$s_tmp = false;
						//foreach($s_notification_options as $s_option) {
							//$s_tmp[] = $options['service']['notification'][$s_option];
						//}
						//$result[$i][]= implode(', ',$s_tmp);

						//$h_notification_options = explode(',',$row->host_notification_options);
						//$h_tmp = false;
						//foreach($h_notification_options as $h_option) {
							//$h_tmp[] = $options['host']['notification'][$h_option];
						//}
						//$result[$i][]= implode(', ',$h_tmp);

						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.($row->host_notification_period == 0 ? _('None') : $row->host_notification_period), $row->service_notification_period == 0 ? _('None') : $row->service_notification_period);
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.($row->host_notification_period == 0 ? _('None') : $row->host_notification_period), $row->host_notification_period == 0 ? _('None') : $row->host_notification_period);
						//$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->{self::SERVICE_NOTIFICATION_COMMANDS}, $row->{self::SERVICE_NOTIFICATION_COMMANDS});
						//$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->{self::HOST_NOTIFICATION_COMMANDS}, $row->{self::HOST_NOTIFICATION_COMMANDS});
						// retention options
						//$ret = false;
						//if ($row->retain_status_information == true) {
							//$ret[] = _('Status Information');
						//}
						//if ($row->retain_nonstatus_information == true) {
							//$ret[] = _('Non-status Information');
						//}
						//$result[$i][] = is_array($ret) ? implode(', ',$ret) : 'None';
						$i++;
					}
					$data = $result;
				}
				break;

			case 'contactgroups': // ********************************************************************
				$header = array(
					_('Group Name'),
					_('Description'),
					_('Contact Members'),
				);

				if ($data!==false) {
					$i = 0;
					foreach($data as $row) {
						$row = (object) $row;

						$result[$i][]= '<a name="'.$row->name.'"></a>'.$row->name;
						$result[$i][]= $row->alias;

						$temp = array();
						foreach ($row->members as $trip) {
							$temp[] = html::anchor(Router::$controller.'/?type=contacts#'.$trip, $trip);
						}
						if($temp) {
							$result[$i][]= implode(', ',$temp);
						} else {
							$result[$i][]= '';
						}
						$i++;
					}
					$data = $result;
				}
				break;

			case 'timeperiods': // ***********************************************************************
				$header = array(
					_('Name'),
					_('Alias/Description'),
					_('Monday Time Ranges'),
					_('Tuesday Time Ranges'),
					_('Wednesday Time Ranges'),
					_('Thursday Time Ranges'),
					_('Friday Time Ranges'),
					_('Saturday Time Ranges'),
					_('Sunday Time Ranges'),
				);
				break;

			case 'commands': // **************************************************************************
				$header = array(
					_('Command Name'),
					_('Command Line')
				);
				break;

			case 'hostgroups': // ***********************************************************************
				$header = array(
					_('Group Name'),
					_('Description'),
					_('Host Members'),
					_('Notes'),
					_('Notes URL'),
					_('Action URL'),
				);

				if ($data!==false) {
					$i = 0;
					foreach($data as $row) {
						$row = (object) $row;
						$result[$i][]= '<a name="'.$row->name.'"></a>'.$row->name;
						$result[$i][]= $row->alias;

						$travel = Livestatus::instance()->getHostsByGroup(array('columns' => 'name', 'filter' => array('name' => $row->name)));
						if ($travel) {
							$temp = false;
							foreach ($travel as $trip) {
								$temp[] = html::anchor(Router::$controller.'/?type=hosts#'.$trip, $trip);
							}
							$result[$i][]= implode(', ',$temp);
						}
						else
							$result[$i][]= '';

						$result[$i][]= $row->notes;
						$result[$i][]= $row->notes_url;
						$result[$i][]= $row->action_url;
						$i++;
						unset($travel);
					}
					$data = $result;
				}
				break;

			case 'servicegroups': // ********************************************************************
				$header = array(
					_('Group Name'),
					_('Description'),
					_('Service Members'),
					_('Notes'),
					_('Notes URL'),
					_('Action URL'),
				);

				if ($data!==false) {
					$i = 0;
					foreach($data as $row) {
						$row = (object) $row;
						$result[$i][]= '<a name="'.$row->name.'"></a>'.$row->name;
						$result[$i][]= $row->alias;

						$travel = Livestatus::instance()->getServicegroups(array('columns' => array('name', 'members'), 'filter' => array('name' => $row->name)));
						$temp = array();
						if ($travel) {
							$travel = current($travel); // getting a nested array here..
							if($travel['members']) {
								foreach ($travel['members'] as $host_service) {
									$temp[] = html::anchor(Router::$controller.'/?type=hosts#'.$host_service[0], $host_service[0]).' / '.html::anchor(Router::$controller.'/?type=services#'.$host_service[1], $host_service[1]);
								}
							}
						}
						if($temp) {
							$result[$i][]= implode(', ',$temp);
						} else {
							$result[$i][]= '';
						}

						$result[$i][]= $row->notes;
						$result[$i][]= $row->notes_url;
						$result[$i][]= $row->action_url;
						$i++;
						unset($travel);
					}
					$data = $result;
				}
				break;
		}

		$this->xtra_js[] = 'application/media/js/jquery.tablesorter.min.js';
		$this->template->js_header = $this->add_view('js_header');
		$this->js_strings .= "var _filter_label = '"._('Enter text to filter')."';";
		$this->template->js_strings = $this->js_strings;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->content->header = $header;
		$this->template->content->data = $data;
		$this->template->content->filter_string = $this->input->get('filterbox', _('Enter text to filter'));
		$this->template->content->type = $this->type;
	}

	public function unauthorized()
	{
		$this->template->content = $this->add_view('extinfo/unauthorized');
		$this->template->disable_refresh = true;

		$this->template->content->error_description = _('If you believe this is an error, check the HTTP server authentication requirements for accessing this page and check the authorization options in your CGI configuration file.');
	}
}
