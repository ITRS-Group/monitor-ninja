<?php

/**
 * Naemon Object Model
 **/
class NaemonObject_Model extends Object_Model {

	/**
	 * Takes a naemon command as first argument, and it's arguments, except
	 * object key, as rest of parameters
	 */
	protected function submit_naemon_command() {
		$args = func_get_args();
		$cmd = array_shift($args);

		if($this instanceof Downtime_Model || $this instanceof Comment_Model) {
			// the downtime|comment models have "id;is_service" as
			// keys, which does not correspond to the Neamon cmd
			$key = $this->get_id();
		} else {
			$key = $this->get_key();
		}
		$key = ($key===false) ? "" : ";".$key;

		$raw_command = sprintf("[%d] %s%s", time(), $cmd, $key);
		foreach($args as $arg) {
			$raw_command .= ";".$arg;
		}

		$output = false;
		try {
			$qh = op5queryhandler::instance();
			$resp = $qh->call("command run", $raw_command, "");
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
		return array(
				'status' => $result,
				'output' => $output=="OK" ? sprintf(_('Your command was successfully submitted to %s.'), Kohana::config('config.product_name')) : $output
				);
	}
}
