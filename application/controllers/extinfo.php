<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Extinfo controller
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Extinfo_Controller extends Authenticated_Controller {
	public $current = false;
	public $logos_path = '';

	public function __construct()
	{
		parent::__construct();

		# load current status for host/service status totals
		$this->current = new Current_status_Model();

		$this->logos_path = Kohana::config('config.logos_path');
	}

	/**
	*	@name details
	*	@desc
	*
	*/
	public function details($type='host', $host=false, $service=false)
	{
		$host =link::decode($host);
		$service = link::decode($service);
		if (empty($host)) {
			return false;
		}

		$this->template->content = $this->add_view('extinfo/index');
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$result = $this->current->object_status($host, $service);
		$host_link = false;
		$yes = $this->translate->_('YES');
		$no = $this->translate->_('NO');

		if ($type == 'host') {
			$group_info = $this->current->get_groups_for_object($type, $result->current()->id);
			$this->template->content->no_group_lable = $this->translate->_('No hostgroups');
			$check_compare_value = Current_status_Model::HOST_CHECK_ACTIVE;
			$last_notification = $result->current()->last_host_notification;
			$this->template->content->lable_next_scheduled_check = $this->translate->_('Next Scheduled Active Check');
			$this->template->content->lable_flapping = $this->translate->_('Is This Host Flapping?');
			$obsessing = $result->current()->obsess_over_host;
		} else {
			$group_info = $this->current->get_groups_for_object($type, $result->current()->service_id);
			$this->template->content->no_group_lable = $this->translate->_('No servicegroups');
			$this->template->content->lable_next_scheduled_check = $this->translate->_('Next Scheduled Check');
			$host_link = html::anchor('extinfo/details/host/'.link::encode($host), html::specialchars($host));
			$check_compare_value = Current_status_Model::SERVICE_CHECK_ACTIVE;
			$last_notification = $result->current()->last_notification;
			$this->template->content->lable_flapping = $this->translate->_('Is This Service Flapping?');
			$obsessing = $result->current()->obsess_over_service;
		}

		$groups = false;
		foreach ($group_info as $group_row) {
			$groups[] = html::anchor(sprintf("status/%sgroup/%s", $type, link::encode($group_row->{$type.'group_name'})),
				html::specialchars($group_row->{$type.'group_name'}));
		}


		$this->template->content->lable_type = $type == 'host' ? $this->translate->_('Host') : $this->translate->_('Service');
		$this->template->content->type = $type;
		$this->template->content->date_format_str = 'Y-m-d H:i:s';
		$this->template->content->host_link = $host_link;
		$this->template->content->lable_member_of = $this->translate->_('Member of');
		$this->template->content->lable_for = $this->translate->_('for');
		$this->template->content->lable_on_host = $this->translate->_('On Host');
		$this->template->content->main_object = $type=='host' ? $host : $service;
		$this->template->content->host = $host;
		$this->template->content->lable_current_status = $this->translate->_('Current Status');
		$this->template->content->lable_status_information = $this->translate->_('Status Information');
		$this->template->content->current_status_str = $this->current->translate_status($result->current()->current_state, $type);
		$this->template->content->duration = $result->current()->duration;
		$this->template->content->groups = $groups;
		$this->template->content->host_address = $result->current()->address;
		$this->template->content->status_info = $result->current()->plugin_output;
		$this->template->content->lable_perf_data = $this->translate->_('Performance Data');
		$this->template->content->perf_data = $result->current()->perf_data;
		$this->template->content->lable_current_attempt = $this->translate->_('Current Attempt');
		$this->template->content->current_attempt = $result->current()->current_attempt;
		$this->template->content->state_type = $result->current()->state_type ? $this->translate->_('HARD state') : $this->translate->_('SOFT state');
		$this->template->content->main_object_alias = $type=='host' ? $result->current()->alias : false;
		$this->template->content->max_attempts = $result->current()->max_attempts;
		$this->template->content->last_update = $result->current()->last_update;
		$this->template->content->last_check = $result->current()->last_check;
		$this->template->content->lable_last_check = $this->translate->_('Last Check Time');
		$this->template->content->lable_check_type = $this->translate->_('Check Type');
		$this->template->content->lable_last_update = $this->translate->_('Last Update');

		$str_active = $this->translate->_('ACTIVE');
		$str_passive = $this->translate->_('PASSIVE');
		$this->template->content->check_type = $result->current()->check_type == $check_compare_value ? $str_active: $str_passive;
		$this->template->content->lable_check_latency_duration = $this->translate->_('Check Latency / Duration');
		$na_str = $this->translate->_('N/A');
		$this->template->content->na_str = $na_str;
		$this->template->content->check_latency =
		$result->current()->check_type == $check_compare_value ? $result->current()->latency : $na_str;
		$this->template->content->execution_time = $result->current()->execution_time;
		$this->template->content->lable_seconds = $this->translate->_('seconds');

		$this->template->content->next_check = (int)$result->current()->next_check;
		$this->template->content->lable_last_state_change = $this->translate->_('Last State Change');
		$this->template->content->last_state_change = (int)$result->current()->last_state_change;
		$this->template->content->lable_last_notification = $this->translate->_('Last Notification');
		$this->template->content->lable_n_a = $na_str;
		$this->template->content->last_notification = $last_notification!=0 ? $last_notification : $na_str;
		$this->template->content->lable_notifications = $this->translate->_('notification');
		$this->template->content->current_notification_number = $result->current()->current_notification_number;
		$lable_flapping_state_change = $this->translate->_('state change');
		$this->template->content->percent_state_change_str = '';
		$is_flapping = $result->current()->is_flapping;
		if (!$result->current()->flap_detection_enabled) {
			$this->template->content->flap_value = $na_str;
		} else {
			$this->template->content->flap_value = $is_flapping ? $yes : $no;
			$this->template->content->percent_state_change_str = '('.number_format((int)$result->current()->percent_state_change, 2).'% '.$lable_flapping_state_change.')';
		}
		$this->template->content->lable_in_scheduled_dt = $this->translate->_('In Scheduled Downtime?');
		$this->template->content->scheduled_downtime_depth = $result->current()->scheduled_downtime_depth ? $yes : $no;
		$last_update_ago_arr = date::timespan(time(), $result->current()->last_update, 'days,hours,minutes,seconds');
		$ago = $this->translate->_('ago');
		$last_update_ago = false;
		$last_update_ago_str = '';
		if (is_array($last_update_ago_arr) && !empty($last_update_ago_arr)) {
			foreach ($last_update_ago_arr as $key => $val) {
				$last_update_ago[] = $val.substr($key, 0, 1);
			}
			$last_update_ago_str = '( '.implode(' ', $last_update_ago) . ' ' . $ago . ')';
		}
		$this->template->content->last_update_ago = $last_update_ago_str !='' ? $last_update_ago_str : $na_str;
		$this->template->content->lable_active_checks = $this->translate->_('Active Checks');
		$this->template->content->lable_passive_checks = $this->translate->_('Passive Checks');
		$this->template->content->lable_obsessing = $this->translate->_('Obsessing');
		$this->template->content->lable_notifications = $this->translate->_('Notifications');
		$this->template->content->lable_event_handler = $this->translate->_('Event Handler');
		$this->template->content->lable_flap_detection = $this->translate->_('Flap Detection');
		$str_enabled = $this->translate->_('ENABLED');
		$str_disabled = $this->translate->_('DISABLED');
		$this->template->content->active_checks_enabled = $result->current()->active_checks_enabled ? $str_enabled : $str_disabled;
		$this->template->content->passive_checks_enabled = $result->current()->passive_checks_enabled ? $str_enabled : $str_disabled;
		$this->template->content->obsessing = $obsessing ? $str_enabled : $str_disabled;
		$this->template->content->notifications_enabled = $result->current()->notifications_enabled ? $str_enabled : $str_disabled;
		$this->template->content->event_handler_enabled = $result->current()->event_handler_enabled ? $str_enabled : $str_disabled;
		$this->template->content->flap_detection_enabled = $result->current()->flap_detection_enabled ? $str_enabled : $str_disabled;

	}

}