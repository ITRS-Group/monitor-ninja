<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Base path of the web site. If this includes a domain, eg: localhost/kohana/
 * then a full URL will be used, eg: http://localhost/kohana/. If it only includes
 * the path, and a site_protocol is specified, the domain will be auto-detected.
 */
$config['site_domain'] = '/ninja/';

/**
 * Force a default protocol to be used by the site. If no site_protocol is
 * specified, then the current protocol is used, or when possible, only an
 * absolute path (with no protocol/domain) is used.
 */
$config['site_protocol'] = '';

/**
 * Name of the front controller for this application. Default: index.php
 *
 * This can be removed by using URL rewriting.
 */
$config['index_page'] = 'index.php';

/**
 * Fake file extension that will be added to all generated URLs. Example: .html
 */
$config['url_suffix'] = '';

/**
 * Length of time of the internal cache in seconds. 0 or FALSE means no caching.
 * The internal cache stores file paths and config entries across requests and
 * can give significant speed improvements at the expense of delayed updating.
 */
$config['internal_cache'] = FALSE;

/**
 * Enable or disable gzip output compression. This can dramatically decrease
 * server bandwidth usage, at the cost of slightly higher CPU usage. Set to
 * the compression level (1-9) that you want to use, or FALSE to disable.
 *
 * Do not enable this option if you are using output compression in php.ini!
 */
$config['output_compression'] = FALSE;

/**
 * Enable or disable global XSS filtering of GET, POST, and SERVER data. This
 * option also accepts a string to specify a specific XSS filtering tool.
 */
$config['global_xss_filtering'] = TRUE;

/**
 * Enable or disable hooks.
 */
$config['enable_hooks'] = true;

/**
 * Log thresholds:
 *  0 - Disable logging
 *  1 - Errors and exceptions
 *  2 - Warnings
 *  3 - Notices
 *  4 - Debugging
 */
$config['log_threshold'] = 0;

/**
 * Message logging directory.
 */
$config['log_directory'] = APPPATH.'logs';

/**
 * Enable or disable displaying of Kohana error pages. This will not affect
 * logging. Turning this off will disable ALL error pages.
 */
$config['display_errors'] = TRUE;

/**
 * Enable or disable statistics in the final output. Stats are replaced via
 * specific strings, such as {execution_time}.
 *
 * @see http://docs.kohanaphp.com/general/configuration
 */
$config['render_stats'] = TRUE;

/**
 * Filename prefixed used to determine extensions. For example, an
 * extension to the Controller class would be named MY_Controller.php.
 */
$config['extension_prefix'] = 'MY_';

$config['autoload'] = array
(
		'libraries' => 'session, database'
);

/**
 * Additional resource paths, or "modules". Each path can either be absolute
 * or relative to the docroot. Modules can include any resource that can exist
 * in your application directory, configuration files, controllers, views, etc.
 */
$config['modules'] = array
(
	MODPATH.'auth',      // Authentication
	// MODPATH.'forge',     // Form generation
	// MODPATH.'kodoc',     // Self-generating documentation
	// MODPATH.'media',     // Media caching and compression
	// MODPATH.'gmaps',     // Google Maps integration
	// MODPATH.'archive',   // Archive utility
	// MODPATH.'payment',   // Online payments
	// MODPATH.'unit_test', // Unit testing
	// MODPATH.'object_db', // New OOP Database library (testing only!)
);

/**
 * 	Base path to the location of Nagios.
 * 	This is used if we need to read some
 * 	configuration from the config files.
 * 	This path sare assumed to contain the
 * 	following subdirectories (unless specified below):
 * 		/bin
 * 		/etc
 * 		/var
 *
 * 	No trailing slash.
 */
$config['nagios_base_path'] = '/opt/monitor';

/**
 *	If the nagios etc directory is to be found outside
 * 	the nagios base path, please specify here.
 *
 * 	No trailing slash.
 */
$config['nagios_etc_path'] = false;

/**
 *	Path to where host logos as stored.
 *	Should be relative to webroot
 */
$config['logos_path'] = '/monitor/images/logos/';

/**
 * Theme config
 *
 * theme_path points to the views subdirectory where ALL
 * available themes are stored
 */
$config['theme_path'] = 'themes/';

/**
 * current_theme is the subdirectory to 'theme_path' above
 * that holds the currently active theme.
 */
$config['current_theme'] = 'default/';

/**
 * Do we use NACOMA (Nagios Configuration Manager)?
 * If path differs from the one below but still installed
 * you could simply change it.
 */
$nacoma_real_path = '/opt/monitor/op5/webconfig/';
if (is_dir($nacoma_real_path)) {
	$config['nacoma_path'] = '/monitor/op5/webconfig/';
} else {
	$config['nacoma_path'] = false;
}

/**
 * Path to Pnp4nagios
 * If installed, change path below or set to false if not
 */
$config['pnp4nagios_path'] = '/monitor/op5/pnp/';

/**
*	File system path to where PNP keeps the perfdata rrd and xml files
*	Only used if 'pnp4nagios_path' !== false
*/
$config['pnp4nagios_config_path'] = '/opt/monitor/etc/pnp/config.php';

/**
 * Do we use NagVis?
 * If path differs from the one below but still installed
 * you could simply change it.
 */
$config['nagvis_real_path'] = '/opt/monitor/op5/nagvis/';
if (is_dir($config['nagvis_real_path'])) {
	$config['nagvis_path'] = '/monitor/op5/nagvis/';
} else {
	$config['nagvis_path'] = false;
}
