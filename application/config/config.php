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
* In case anyone would like to brand their installation
* This string is shown throughout the GUI in various places
* and this is the only place you will have to change it.
*/
$config['product_name'] = 'Nagios';

/**
 * Custom version info file. Format:
 * VERSION=x.y.z
 * This info will be visible in the 'product info' link
 */
$config['version_info'] = '/etc/ninja-release';

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
$config['log_threshold'] = 4;

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
 * current_skin is the subdirectory to 'css' within the
 * theme. a skin a simple way of altering colours etc
 * in the gui.
 */
$config['current_skin'] = 'default/';

/**
 * Do we use NACOMA (Nagios Configuration Manager)?
 * If path differs from the one below but still installed
 * you could simply change it.
 */
$nacoma_real_path = '/opt/monitor/op5/nacoma/';
if (is_dir($nacoma_real_path)) {
	$config['nacoma_path'] = '/monitor/op5/nacoma/';
} else {
	$config['nacoma_path'] = false;
}

/**
 * Web path to Pnp4nagios
 * If installed, change path below or set to false if not
 */
$config['pnp4nagios_path'] = '/monitor/op5/pnp/';

/**
*	Path to the pnp config file 'config.php'
*	Only used if 'pnp4nagios_path' !== false
*/
$config['pnp4nagios_config_path'] = '/opt/monitor/etc/pnp/config.php';
if (!is_file($config['pnp4nagios_config_path'])) {
	$config['pnp4nagios_path'] = false;
	$config['pnp4nagios_config_path'] = false;
}

/**
 * Do we use NagVis?
 * If path differs from the one below but still installed
 * you could simply change it.
 */
/* remove hardcoded nagvis menu entry
$config['nagvis_real_path'] = '/opt/monitor/op5/nagvis/';
if (is_dir($config['nagvis_real_path'])) {
	$config['nagvis_path'] = '/monitor/op5/nagvis/';
} else {
}
*/
$config['nagvis_path'] = false;

/**
* Add some suport for cacti/statistics
*/
$condition['cacti_real_path'] = '/opt/statistics';
if (is_dir($condition['cacti_real_path'])) {
	$config['cacti_path'] = true;
} else {
	$config['cacti_path'] = false;
}

/**
 * Default refresh rate for all pages
 */
$config['page_refresh_rate'] = 90;

/**
 * Control command line access to Ninja
 * Possible values:
 * 	false 		: 	No commandline access
 * 	true		:	Second command line argument (i.e after path)
 * 					will be used as username (default)
 * 	'username'	:	The entered username will be used for authentication
 */
$config['cli_access'] = true;

/**
* Nr of items returned for searches
*/
$config['search_limit'] = 10;

/**
* Nr of items returned for autocomplete search
*/
$config['autocomplete_limit'] = 10;

/**
* 	Nr of seconds while we still are considering
* 	merlin to be alive.
*/
$config['stale_data_limit'] = 60;

/**
* Control the use oof pop-ups for PNP graphs and comments
*/
$config['use_popups'] = 0;

/**
* Pop-up delay
* Milliseconds before the pop-up is shown
*/
$config['popup_delay'] = 1500;

/**
* Control whether to show display_name or not
*/
$config['show_display_name'] = 1;

/**
* Control whether to show {host,service} notes or not
* Default: 0
*/
$config['show_notes'] = 0;

/**
* 	Control how many characters of the note to be displayed
* 	in the GUI. The entire note will be displayed on mouseover or click.
* 	Use 0 to display everything.
* 	Default: 80
*/
$config['show_notes_chars'] = 80;

/**
*  Controls if you will display multiline output or not
*  in the Service detail view.
*/
$config['service_long_output_enabled'] = false;

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
