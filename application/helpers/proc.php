<?php

/**
 * Replacement for PHP's exec() that separates stdout & stderr.
 */
class proc {

	/**
	 * @param $command array
	 * @param &$stdout
	 * @param &$stderr
	 * @param &$exit_code
	 * @return boolean whether the command was *called* successfully or not
	 * (according to PHP), not caring about subprocess' exit status
	 */
	static function open(array $command, &$stdout = NULL, &$stderr = NULL, &$exit_code = NULL) {
		$stdout = NULL;
		$stderr = NULL;
		$exit_code = NULL;

		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);

		$cmd_string = implode(' ', array_map('escapeshellarg', $command));
		$resource = proc_open($cmd_string, $descriptorspec, $pipes);
		if(!is_resource($resource)) {
			return false;
		}

		fclose($pipes[0]);

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);

		fclose($pipes[1]);
		fclose($pipes[2]);

		$exit_code = proc_close($resource);
		return true;
	}
}

