<?php defined('SYSPATH') OR die('No direct access allowed.');

class Cli_Controller extends Authenticated_Controller {

	public function __construct()
	{
		# Only grant permission for cli access or if
		# user has been given the ADMIN role
		if (PHP_SAPI !== "cli" &&
			!Auth::instance()->logged_in(Ninja_Controller::ADMIN)) {
			url::redirect('default/index');
		}
	}

	/**
	*	Takes input from commandline import of cgi.cfg
	*/
	public function edit_user_authorization()
	{
		$options = $this->_parse_parameters();
		$user = new User_Model();
		$result = $user->user_auth_data($options['user'], $options['authdata']);
		print_r($result);
		return $result;
	}

	/**
	 * Parse input data from commandline and stores in an array
	 * An equivalent to getopt() but easier for us in this environment
	 */
	public function _parse_parameters($noopt = array())
	{
		$result = array();
		$params = $GLOBALS['argv'];
		// could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
		reset($params);
		while (list($tmp, $p) = each($params)) {
			if ($p{0} == '-') {
				$pname = substr($p, 1);
				$value = true;
				if ($pname{0} == '-') {
					// long-opt (--<param>)
					$pname = substr($pname, 1);
					if (strpos($p, '=') !== false) {
						// value specified inline (--<param>=<value>)
						list($pname, $value) = explode('=', substr($p, 2), 2);
					}
				}
				// check if next parameter is a descriptor or a value
				$nextparm = current($params);
				if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-')
					list($tmp, $value) = each($params);
				$result[$pname] = $value;
			} else {
			// param doesn't belong to any option
			$result[] = $p;
			}
		}
		return $result;
	}
}