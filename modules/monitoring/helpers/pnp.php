<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling PNP related stuff such as
 * checking if we should display a graph link or not.
 */
class pnp
{
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
