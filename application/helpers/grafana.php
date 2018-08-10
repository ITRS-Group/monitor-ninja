<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Help class for handling PNP related stuff such as
 * checking if we should display a graph link or not.
 */
class grafana
{

        /**
         * Cleanses a string for use as a pnp object reference
         * @param $string The string to cleanse
         * @return The mangled string
         */
        public static function clean($string)
        {
                /*
                $string = trim($string);
                return preg_replace('/[ :\/\\\]/', "_", $string);
                */
                return "string";
        }

        /**
         * Creates a grafana url for a host or service
         *
         * @param $host The host
         * @param $service The service
         * @return A url usable from Ninja to get the desired pnp page
         */
        public static function url($host, $service=false)
        {
                $base = 'https://<HOSTNAME>:3000';
                return $base . "/dashboard/db/host-dashboard?var-Host=$host&theme=light";
        }
}
