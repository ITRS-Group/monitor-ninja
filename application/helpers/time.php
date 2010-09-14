<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for converting seconds to readable string of days, hours etc
 */
class time_Core
{
	public $translate = false;
	public function __construct()
	{
		# fetch our translation instance
		$this->translate = zend::instance('Registry')->get('Zend_Translate');
	}

	/**
	 * Convert a given nr of sec to string,
	 * day, hour, minute, second
	 */
	function to_string($t=0)
	{
		$neg = false;

		# translate the abbreviations
		# seems weird but I suppose someone will want this anyway
		$d = $this->translate->_('d'); // day
		$h = $this->translate->_('h'); // hour
		$m = $this->translate->_('m'); // minute
		$s = $this->translate->_('s'); // second
		$negative = $this->translate->_("negative") . " ";

		if (!$t) return "0$d 0$h 0$m 0$s";
		if ($t < 0) {
			$neg 	= 1;
			$t 		= 0 - $t;
		}

		$days 	= $t / 86400;
		$days 	= floor($days);
		$hrs 	= ($t / 3600) % 24;
		$mins 	= ($t / 60) % 60;
		$secs 	= $t % 60;

		$timestring = "";
		if ($neg) $timestring .= $negative;
		if ($days) {
			$timestring .= $days.$d;
			if ($hrs || $mins || $secs) $timestring .= " ";
		}
		if ($hrs) {
			$timestring .= $hrs.$h;
			if ($mins && $secs) $timestring .= " ";
		}
		if ($mins) {
			$timestring .= $mins.$m;
			if ($mins && $secs) $timestring .= " ";
		}
		if ($secs) $timestring .= $secs.$s;
		return $timestring;
	}

}
