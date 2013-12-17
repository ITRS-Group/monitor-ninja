<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Availability reports controller
 */
class Avail_Controller extends Reports_Controller
{
	public $type = 'avail';
	/**
	 * Translated helptexts for this controller
	 */
	public static function _helptexts($id)
	{
		$helptexts = array(
			'status_to_display' => _('Checking a state here causes it to be hidden from the report. You might find that hiding e.g. all states except critical creates a clearer report.'),
		);
		if (array_key_exists($id, $helptexts))
			echo $helptexts[$id];
		else
			parent::_helptexts($id);

	}
}
