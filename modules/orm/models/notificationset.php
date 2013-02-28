<?php

require_once( dirname(__FILE__).'/base/basenotificationset.php' );

/**
 * Describes a set of objects from livestatus
 */
class NotificationSet_Model extends BaseNotificationSet_Model {
	protected function get_auth_filter() {
		$auth = Auth::instance();
		$all_hosts    = $auth->authorized_for('host_view_all');
		$all_services = $auth->authorized_for('service_view_all');
		$contact_name = $auth->get_user()->username;

		// Authorized for everything? Don't filter anything...
		if( $all_hosts && $all_services )
			return $this->filter;

		$auth_filter = new LivestatusFilterOr();
		$auth_filter->add(new LivestatusFilterMatch('contact_name', $contact_name));

		if( $all_hosts ) {
			$auth_filter->add( new LivestatusFilterMatch('notification_type', nagstat::HOST_NOTIFICATION));
		}
		if( $all_services ) {
			$auth_filter->add( new LivestatusFilterMatch('notification_type', nagstat::SERVICE_NOTIFICATION));
		}

		$result_filter = new LivestatusFilterAnd();
		$result_filter->add($this->filter);
		$result_filter->add($auth_filter);
		return $result_filter;
	}
}
