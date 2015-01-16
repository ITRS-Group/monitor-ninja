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
			'time_format' => "Whether to display the time a host or a service has been in a state as relative percentages, absolute time, or both.",
		);
		if (array_key_exists($id, $helptexts))
			echo $helptexts[$id];
		else
			parent::_helptexts($id);
	}
}
