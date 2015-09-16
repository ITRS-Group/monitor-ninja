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
		# fetch widget view path
		$view_path = $this->view_path('view');

		# fetch info on outages
		$current_status = $this->get_current_status();
		#$outages = new Outages_Model();
		#$outage_data = $outages->fetch_outage_data();

		$label = _('Blocking Outages');
		$no_access_msg = _('N/A');

		$total_blocking_outages = $current_status->hst->outages;

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
