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
	/**
	 * Which type to show
	 */
	public $type = 'hosts';
	/*
	const SERVICE_NOTIFICATION_COMMANDS =  'service_notification_commands';
	const HOST_NOTIFICATION_COMMANDS = 'host_notification_commands';
	*/

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
		$items_per_page = $this->input->get('items_per_page', config::get('pagination.default.items_per_page'));
		$pagination = new CountlessPagination(array('items_per_page' => $items_per_page));

		$filter = $this->input->get('filterbox', null);

		// Object types used on this page paired with their columns for filter
		// matching.
		$obj_types = array(
			"hosts"         => array("name"),
			"hostgroups"    => array("name"),
			"services"      => array("description", "host.name"),
			"servicegroups" => array("name"),
			"contacts"      => array("name"),
			"contactgroups" => array("name"),
			"timeperiods"   => array("name"),
			"commands"      => array("name", "line")
		);

		$pool = ObjectPool_Model::pool($this->type);
		if ($pool !== false) {
			// Find filter matches in all given columns for the current object
			// type, and merge the result to $set.
			$set = $pool->none();
			$columns = $obj_types[$this->type];
			foreach ($columns as $column) {
				$set = $set->union($pool->all()->reduce_by(
					$column, $filter, "~~"
				));
			}

			if (!$this->mayi->run($set->mayi_resource().":read.list.config", array(), $messages)) {
				$set = $pool->none();
			}
			foreach ($messages as $m) {
				$this->notices[] = new ErrorNotice_Model($m);
			}
			try {
				$data = $set->it(
					false, array(), $pagination->items_per_page,
					($pagination->current_page-1)*$pagination->items_per_page
				);
			} catch(ORMException $e) {
				$this->notices[] = new ErrorNotice_Model($e->getMessage());
				$data = array();
			}

			$i = 0;
			$result=array();
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

					foreach($data as $row) {
						$row = (object) $row;
						$result[$i][]= '<a name="'.$row->get_name().'"></a>'.$row->get_name();
						$result[$i][]= $row->get_alias();
						$result[$i][]= $row->get_display_name();
						$result[$i][]= $row->get_address();
						$tmp = array();
						foreach ($row->get_parents() as $parent) {
							$tmp[] = html::anchor(Router::$controller.'/?type=hosts#'.$parent, $parent);
						}
						$result[$i][] = implode(', ',$tmp);
						$result[$i][]= $row->get_max_check_attempts();
						$result[$i][]= time::to_string($row->get_check_interval()*60);
						$result[$i][]= time::to_string($row->get_retry_interval()*60);
						$result[$i][]= html::anchor(Router::$controller.'/?type=commands#'.$row->get_check_command(), $row->get_check_command());
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->get_check_period(), $row->get_check_period());
						$result[$i][]= $row->get_obsess() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_active_checks_enabled() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_accept_passive_checks() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_check_freshness() == 1 ? _('Yes') : _('No');
						//$result[$i][]= $row->get_freshness_threshold() == 0 ? _('Auto-determined value') : $row->get_freshness_threshold().' '._('seconds');
						$c_link = array();
						foreach($row->get_contact_groups() as $cg){
							$c_link[] = html::anchor(Router::$controller.'/?type=contactgroups#'.$cg, $cg);
						}
						foreach($row->get_contacts() as $c){
							$c_link[] = html::anchor(Router::$controller.'/?type=contacts#'.$c, $c);
						}
						$result[$i][] = implode(', ', $c_link);

						$result[$i][]= $row->get_notification_interval() == 0 ? _('No Re-notification') : $row->get_notification_interval();
						$result[$i][]= time::to_string($row->get_first_notification_delay());
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->get_notification_period(), $row->get_notification_period());
						$result[$i][]= $row->get_event_handler() == 0 ? '&nbsp;' : $row->get_event_handler();
						$result[$i][]= $row->get_event_handler_enabled() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_flap_detection_enabled() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_low_flap_threshold() == 0.0 ? _('Program-wide value') : $row->get_low_flap_threshold();
						$result[$i][]= $row->get_high_flap_threshold() == 0.0 ? _('Program-wide value') : $row->get_high_flap_threshold();
						$result[$i][]= $row->get_process_performance_data() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_notes();
						$result[$i][]= $row->get_notes_url();
						$result[$i][]= $row->get_action_url();
						$result[$i][]= $row->get_icon_image();
						$result[$i][]= $row->get_icon_image_alt();
						$i++;
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
						//_('Retention Options'),
					);

					foreach($data as $row) {
						$row = (object) $row;

						$result[$i][]= '<a name="'.$row->get_host()->get_name().'"></a>'.$row->get_host()->get_name();
						$result[$i][]= '<a name="'.$row->get_description().'"></a>'.$row->get_description();
						$result[$i][]= $row->get_max_check_attempts();
						$result[$i][]= time::to_string($row->get_check_interval()*60);
						$result[$i][]= time::to_string($row->get_retry_interval()*60);
						$result[$i][]= $row->get_check_command();
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->get_check_period(), $row->get_check_period());
						$result[$i][]= $row->get_obsess() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_active_checks_enabled() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_accept_passive_checks() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_check_freshness() == 1 ? _('Yes') : _('No');

						$c_link = array();
						foreach($row->get_contact_groups() as $cg){
							$c_link[] = html::anchor(Router::$controller.'/?type=contactgroups#'.$cg, $cg);
						}
						foreach($row->get_contacts() as $c){
							$c_link[] = html::anchor(Router::$controller.'/?type=contacts#'.$c, $c);
						}
						$result[$i][] = implode(', ', $c_link);

						$result[$i][]= $row->get_notifications_enabled() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_notification_interval() == 0 ? _('No Re-notification') : $row->get_notification_interval();
						$result[$i][]= time::to_string($row->get_first_notification_delay());
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->get_notification_period(), $row->get_notification_period());
						$result[$i][]= $row->get_event_handler() == 0 ? '&nbsp;' : $row->get_event_handler();
						$result[$i][]= $row->get_event_handler_enabled() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_flap_detection_enabled() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_low_flap_threshold() == 0.0 ? _('Program-wide value') : $row->get_low_flap_threshold();
						$result[$i][]= $row->get_high_flap_threshold() == 0.0 ? _('Program-wide value') : $row->get_high_flap_threshold();
						$result[$i][]= $row->get_process_performance_data() == 1 ? _('Yes') : _('No');
						$result[$i][]= $row->get_notes();
						$result[$i][]= $row->get_notes_url();
						$result[$i][]= $row->get_action_url();
						$result[$i][]= $row->get_icon_image();
						$result[$i][]= $row->get_icon_image_alt();
						$i++;
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

					foreach($data as $row) {
						$row = (object) $row;
						$result[$i][]= '<a name="'.$row->get_name().'"></a>'.$row->get_name();
						$result[$i][]= $row->get_alias();
						$result[$i][]= '<a href="mailto:'.$row->get_email().'">'.$row->get_email().'</a>';
						$result[$i][]= $row->get_pager();

						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->get_host_notification_period(), $row->get_service_notification_period());
						$result[$i][]= html::anchor(Router::$controller.'/?type=timeperiods#'.$row->get_host_notification_period(), $row->get_host_notification_period());

						$i++;
					}
					break;

				case 'contactgroups': // ********************************************************************
					$header = array(
						_('Group Name'),
						_('Description'),
						_('Contact Members'),
					);

					foreach($data as $row) {
						$row = (object) $row;

						$result[$i][]= '<a name="'.$row->get_name().'"></a>'.$row->get_name();
						$result[$i][]= $row->get_alias();

						$temp = array();
						foreach ($row->get_members() as $trip) {
							$temp[] = html::anchor(Router::$controller.'/?type=contacts#'.$trip, $trip);
						}
						if($temp) {
							$result[$i][]= implode(', ',$temp);
						} else {
							$result[$i][]= '';
						}
						$i++;
					}
					break;

				case 'timeperiods': // ***********************************************************************
					$header = array(
						_('Name'),
						_('Alias/Description'),
						_('In effect?'),
					);

					foreach($data as $row) {
						$result[$i][] = $row->get_name();
						$result[$i][] = $row->get_alias();
						$result[$i][] = $row->get_in() ? _('Yes') : _('No');
						$i++;
					}
					break;

				case 'commands': // **************************************************************************
					$header = array(
						_('Command Name'),
						_('Command Line')
					);

					foreach($data as $row) {
						$result[$i][] = $row->get_name();
						$result[$i][] = $row->get_line();
						$i++;
					}
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

					foreach($data as $row) {
						$row = (object) $row;
						$result[$i][]= '<a name="'.$row->get_name().'"></a>'.$row->get_name();
						$result[$i][]= $row->get_alias();

						$temp = array();
						foreach ($row->get_members() as $trip) {
							$temp[] = html::anchor(Router::$controller.'/?type=hosts#'.$trip, $trip);
						}
						$result[$i][]= implode(', ',$temp);

						$result[$i][]= $row->get_notes();
						$result[$i][]= $row->get_notes_url();
						$result[$i][]= $row->get_action_url();
						$i++;
						unset($travel);
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

					foreach($data as $row) {
						$row = (object) $row;
						$result[$i][]= '<a name="'.$row->get_name().'"></a>'.$row->get_name();
						$result[$i][]= $row->get_alias();

						$temp = array();
						foreach ($row->get_members() as $host_service) {
							$temp[] = html::anchor(Router::$controller.'/?type=hosts#'.$host_service[0], $host_service[0]).' / '.html::anchor(Router::$controller.'/?type=services#'.$host_service[1], $host_service[1]);
						}
						if($temp) {
							$result[$i][]= implode(', ',$temp);
						} else {
							$result[$i][]= '';
						}

						$result[$i][]= $row->get_notes();
						$result[$i][]= $row->get_notes_url();
						$result[$i][]= $row->get_action_url();
						$i++;
						unset($travel);
					}
					break;
			}
		} else {
			$result = false;
			$header = false;
		}
		$this->template->title = _('Configuration').' » '._('View config');
		$this->template->content = $this->add_view('config/index');

		$this->template->js_strings = $this->js_strings;
		$this->template->content->header = $header;
		$this->template->content->data = $result;
		if(!$result || count($result) < $pagination->items_per_page) {
			$pagination->hide_next = true;
		}
		$this->template->content->pagination = $pagination;
		$filter_string = $this->input->get('filterbox');
		$this->template->content->filter_string = $filter_string;

		$this->template->content->type = $this->type;
		$this->template->toolbar = new Toolbar_Controller( _( "View Config" ), _('Object type') );

		$obj_form = '<form method="get" action="">';
		$obj_form .= ' <select class="auto" name="type" onchange="submit()">';

		foreach ($obj_types as $t => $columns) {
			if ( $t === $this->type ) {
				$obj_form .= '<option value="' . $t . '" selected="selected">' . $t . '</option>';
			} else {
				$obj_form .= '<option value="' . $t . '">' . $t . '</option>';
			}
		}

		ob_start();
		?>
		</select>
		  <input type="text" id="filterbox" name="filterbox" placeholder=
		    "<?php echo html::specialchars(_('Enter text to filter')); ?> "
		    value="<?php echo html::specialchars($filter_string); ?>" />
		  <input type="submit" value="<?php echo _("Filter"); ?>" />
		</form>
		<?php
		$obj_form .= ob_get_clean();

		$this->template->toolbar->info( $obj_form );
	}

	/**
	 * Show a page that you are not autoraized
	 */
	public function unauthorized()
	{
		$this->template->content = $this->add_view('extinfo/unauthorized');
		$this->template->disable_refresh = true;

		$this->template->content->error_description = _('If you believe this is an error, check the HTTP server authentication requirements for accessing this page and check the authorization options in your CGI configuration file.');
	}
}
