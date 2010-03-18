<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling PNP related stuff such as
 * checking if we should display a graph link or not.
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class pnp_Core
{
	/**
	*	Check that there is actually a graph to display
	*	and that we can show a link to it.
	*/
	public static function has_graph($host=false, $service=false)
	{
		if (empty($host)) {
			return false;
		}
		if (!self::is_enabled()) {
			return false;
		}
		$host = trim($host);
		$service = trim($service);
		$rrdbase = self::pnp_config('rrdbase');
		if (empty($rrdbase)) {
			# config missing or some other error
			return false;
		}

		$rrdbase = trim($rrdbase);

		# Better safe than sorry...
		if (substr($rrdbase, -1, 1) != '/') {
			$rrdbase .= '/';
		}

		if (empty($service)) {
			$service = '_HOST_';
		} else {
			# replace some strings in service name
			# like PNP does
			$service = urldecode($service);
			$service = preg_replace('/[ :\/\\\\]/', "_", $service);
		}

		$path = $rrdbase . $host . '/' . $service . '.rrd';

		return posix_access($path, POSIX_R_OK);
	}

	/**
	*	Check if PNP is installed (enabled) on this machine
	*/
	public static function is_enabled()
	{
		$pnp_path = config::get('config.pnp4nagios_path');
		return $pnp_path === false ? false : true;
	}

	/**
	*	Fetch PNP config options and stash in current session.
	* 	Returns the value of $key or entire config if no params
	*/
	public static function pnp_config($key=false)
	{
		$conf = Session::instance()->get('pnp_config', false);

		if (empty($conf)) {
			# PNP config file consists of PHP code which makes it possible
			# for us to just include it to get options available in $conf array
			$pnp_config_file = Kohana::config('config.pnp4nagios_config_path');
			if (file_exists($pnp_config_file))
				include_once($pnp_config_file);

			# Since the PNP is not very likely to change very often,
			# we may store the config in session to save us from
			# fetching it more than once per session.
			Session::instance()->set('pnp_config', $conf);
		}
		return empty($key) ? $conf : $conf[$key];
	}
}