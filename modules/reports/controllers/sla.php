<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * SLA reports controller
 */
class Sla_Controller extends Reports_Controller
{
	public $type = 'sla';

	/**
	 * Get the earliest timestamp found in the report database, or fallback
	 * to the current timestamp.
	 */
	public function custom_start() {
		$row = Database::instance()->query("SELECT MIN(timestamp) as timestamp from report_data");
		if(!$row) {
			return json::ok(array('timestamp' => date()));
		}
		$value = $row->result(false)->current();
		return json::ok(array('timestamp' => $value['timestamp']));
	}

	/**
	 * Returns a json object which describes the months that should be set for
	 * this report id.
	 */
	public function per_month_sla_for_report() {
		$id = $input->get('id');
		$opts = Report_options::setup_options_obj('sla', array('report_id' => $id));
		if (!$opts['months'])
			return json::fail(array('reason' => "Couldn't find SLA report with id $id"));
		return json::ok(array('months' => $opts['months']));
	}
}
