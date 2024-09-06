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
		$cmd_string = implode(' ', array_map('escapeshellarg', $command));
		$raw_output = proc::raw($cmd_string, $stdout, $stderr, $exit_code);
		return $raw_output;
	}

	/**
	 * Our product supports input of full commandlines by users, these can not
	 * be escaped as proc::open does, hence we require a proc::raw instead of
	 * using exec/system where we are unable to get the stderr output stream.
	 *
	 * @param $command string
	 * @param &$stdout
	 * @param &$stderr
	 * @param &$exit_code
	 * @return boolean whether the command was *called* successfully or not
	 * (according to PHP), not caring about subprocess' exit status
	 */
	static function raw ($command, &$stdout = NULL, &$stderr = NULL, &$exit_code = NULL) {

		$stdout = NULL;
		$stderr = NULL;
		$exit_code = NULL;

		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);

		$resource = proc_open($command, $descriptorspec, $pipes);

		echo "Resource: ";
		var_dump($resource);

		if(!is_resource($resource)) {
			return false;
		}

		fclose($pipes[0]);

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);

		fclose($pipes[1]);
		fclose($pipes[2]);

		echo "Resource Output: ";
		var_dump($stdout);
		echo "Resource Error: ";
		var_dump($stderr);

		$exit_code = proc_close($resource);
		return true;

	}

}

