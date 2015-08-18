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

	/**
	 * @param $object Object_Model
	 * @param $command string
	 * @param $text string
	 * @return string|null
	 */
	static function cmd_link(Object_Model $object, $command, $text) {
		$command_list = $object->list_commands();
		if(!array_key_exists($command, $command_list))
			return null;
		if(!array_key_exists('mayi_method', $command_list[$command]))
			return null;
		return html::anchor(
			sprintf(
				"cmd?command=%s&table=%s&object=%s",
				urlencode($command),
				urlencode($object->get_table()),
				urlencode($object->get_key())
			),
			html::specialchars($text)
		);
	}


	/**
	 * Helper function to save us from typing
	 * the links to the cmd controller
	 *
	 */
	static function command_link($command_type=false, $host=false, $service=false, $lable='', $method='submit', $force=false, $attributes=NULL)
	{
		$host = trim($host);

		$lable = trim($lable);
		$method = trim($method);
		if ($command_type===false || empty($lable) || empty($method)) {
			return false;
		}
		$lnk = "command/$method?cmd_typ=$command_type";
		# only print extra params when present
		if (!empty($host)) {
			$lnk .= '&host_name=' . urlencode($host);
		}
		if (!empty($service)) {
			$lnk .= '&service=' . urlencode($service);
		}
		if ($force === true) {
			$lnk .= '&force=true';
		}

		return html::anchor($lnk, html::specialchars($lable), $attributes);
	}

	/**
	 * Returns the HTML for a button which can send commands through ajax
	 * instead of page reloads!
	 *
	 * TODO Handling of command parameters
	 */
	static function command_ajax_button ( $command, $lable, $params = false, $state = false ) {

		$user = Auth::instance()->get_user();

		if ($user->authorized_for('system_commands')) {
			if ( $params != false && is_array( $params ) ) {
				$params = json_encode( $params );
				return '<button class="command-button" ' . (( $state !== false ) ? ('data-state="' . $state . '"') : "") . ' data-parameters="' . $params . '" data-command="' . $command . '"><span></span>' . $lable . '</button>';
			}
			return '<button class="command-button" ' . (( $state !== false ) ? ('data-state="' . $state . '"') : "") . ' data-command="' . $command . '"><span></span>' . $lable . '</button>';
		} else {
			return '<button class="command-button" ' . (( $state !== false ) ? ('data-state="' . $state . '"') : "") . ' disabled="disabled" title="' . _('You are not authorized for system commands.') . '"><span></span>' . $lable . '</button>';
		}

	}
}
