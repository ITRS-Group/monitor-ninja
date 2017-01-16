<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tools for support to use on existing systems
 */
class Support_Controller extends Ninja_Controller
{
	/**
	 * System information
	 *
	 * Get overall inforamtion about the system. The same information as available
	 * in bottom of stack traces
	 */
	public function sysinfo()
	{
		$this->_verify_access('monitor.support.sysinfo:read.gui');
		$commands = Kohana::config('exception.shell_commands', array());
		$data = array();
		foreach($commands as $command) {
			$output = array();
			exec($command, $output, $exit_value);
			$data[$command] = implode("\n", $output);
		}
		$this->template = new View('support/plainlist', array(
			'data' => $data
		));
	}
}
