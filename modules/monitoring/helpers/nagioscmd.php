<?php

require_once('op5/queryhandler.php');

/**
 * Nagios FIFO command helper
 */
class nagioscmd
{
	/**
	 * Actually submit command to nagios
	 * @param $cmd The complete command
	 * @param $args Not at all a string, and I suspect you can't use it
	 * @param $output string If a response was received from naemon, the message will be set here
	 * @return boolean false on error, else true
	 */
	public static function submit_to_nagios($cmd, $args = "", &$output = false)
	{
		$output = false;
		$qh = op5queryhandler::instance();
		$command = sprintf("[%d] %s", time(), $cmd);
		try {
			$resp = $qh->call("command run", $command, $args);
			$output = $resp;
		} catch (op5queryhandler_Exception $e) {
			op5log::instance("ninja")->log("error", "external command failed. Exception: " . $e->getMessage());
			return false;
		}
		# because there are two command modules, with different output:
		$result = ($output === "200: OK" || substr($output, 0, strlen("OK:")) === "OK:");
		if ($output >= 100 && $output < 1000) #yes, implicit cast to int - am I PHPing right?
			$output = substr($output, 5); # remove pseudo-HTTP code
		if(!$result) {
			op5log::instance("ninja")->log("error", "external command failed. Output: " . trim($output));
		}
		return $result;
	}
}
