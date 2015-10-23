<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network outages widget
 *
 * @author     op5 AB
 */
class Netw_outages_Widget extends widget_Base {
	protected $duplicatable = true;
	public function index()
	{
		$view_path = $this->view_path('view');
		$total_blocking_outages = 0;
		try {
			$current_status = Current_status_Model::instance();
			$current_status->analyze_status_data();
			$total_blocking_outages = $current_status->hst->outages;
		}
		catch (op5LivestatusException $ex) {
		}

		$label = _('Blocking Outages');
		$no_access_msg = _('N/A');


		$user_has_access = op5auth::instance()->authorized_for('host_view_all');

		require($view_path);
	}

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Network outages',
			'instanceable' => true
		));
	}
}
