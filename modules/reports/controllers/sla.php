<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * SLA reports controller
 */
class Sla_Controller extends Reports_Controller
{
	public $type = 'sla';
	/**
	 * Translated helptexts for this controller
	 */
	public static function _helptexts($id)
	{
		$helptexts = array(
			'status_to_display' => _('Checking a state here causes it to not decrease the SLA. If e.g. warnings are allowed under the SLA conditions that apply, you should hide warning.'),
		);
		if (array_key_exists($id, $helptexts))
			echo $helptexts[$id];
		else
			parent::_helptexts($id);
	}

	public function custom_start() {
		$row = Database::instance()->query("SELECT MIN(timestamp) as timestamp from report_data");
		if(!$row) {
				return json::ok(date());
		}
		$value = $row->result(false)->current();
		return json::ok(array('timestamp' => $value['timestamp']));
	}

}
