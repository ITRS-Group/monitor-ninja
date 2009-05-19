<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Sends external commands to Nagios command FIFO
 */
class Command_Model extends Model
{
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	 * Obtain command information
	 * Complete with information and data needed to request input
	 * regarding a particular command
	 * @param $cmd The name (or 'id') of the command
	 * @return Indexed array
	 */
	public function get_command_info($cmd)
	{
		$info = nagioscmd::cmd_info($cmd);
		# we need the template to get the information we need
		if (empty($info) || !isset($info['template'])) {
			return false;
		}

		$raw_params = array_slice(explode(';', $info['template']), 1);
		$params = array();
		foreach ($raw_params as $param_name) {
			# reset between each loop
			$ary = array();

			# populate $params[$param_name] here
			$params[$param_name] = array();
		}
		$info['params'] = &$params;
	}
}
