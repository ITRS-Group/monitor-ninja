<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling PNP related stuff such as
 * checking if we should display a graph link or not.
 */
class pnp_Core
{
	/**
	*	Check that there is actually a graph to display
	*	and that we can show a link to it.
	*/
	public static function has_graph($host=false, $service=false)
	{
		return !!self::get_sources($host, $service);
	}

	public static function get_sources($host=false, $service=false)
	{
		if (empty($host)) {
			return array();
		}
		if (!self::is_enabled()) {
			return array();
		}
		$host = self::clean($host);
		$rrdbase = self::pnp_config('rrdbase');
		if (empty($rrdbase)) {
			# config missing or some other error
			return array();
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
			$service = self::clean($service);
		}

		$path = $rrdbase . $host . '/' . $service . '.xml';

		if (!posix_access($path, POSIX_R_OK))
			return array();

		$contents = file_get_contents($path);
		$xmldata = @simplexml_load_string($contents);
		if ($xmldata === false) {
			// one can always try...
			$xmldata = @simplexml_load_string(utf8_encode($contents));
		}
		$res = array();
		if ($xmldata->DATASOURCE) {
			foreach ($xmldata->DATASOURCE as $ds) {
				if (isset($ds->DS))
					$res[] = ((int)$ds->DS) - 1;
			}
		}
		return $res;
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

	/**
	 * Cleanses a string for use as a pnp object reference
	 * @param $string The string to cleanse
	 * @return The mangled string
	 */
	public static function clean($string)
	{
		$string = trim($string);
		return preg_replace('/[ :\/\\\]/', "_", $string);
	}

	/**
	 * Creates a pnp url for a host or service
	 *
	 * @param $host The host
	 * @param $service The service
	 * @return A url usable from Ninja to get the desired pnp page
	 */
	public static function url($host, $service=false)
	{
		$base = config::get('config.pnp4nagios_path');
		# luls hackish
		if (!$base)
			return 'PNP_seems_to_be_improperly_configured';

		$host = urlencode(pnp::clean($host));
		if ($service !== false) {
			$service = urlencode(pnp::clean($service));
		} else {
			$service = '_HOST_';
		}
		return $base . "/graph?host=$host&srv=$service";
	}
}
