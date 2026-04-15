<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * Domain, to restrict the cookie to a specific website domain. For security,
 * you are encouraged to set this option. An empty setting allows the cookie
 * to be read by any website domain.
 */
$config['domain'] = '';

/**
 * Restrict cookies to a specific path, typically the installation directory.
 */
$config['path'] = '/';

/**
 * Lifetime of the cookie. A setting of 0 makes the cookie active until the
 * users browser is closed or the cookie is deleted.
 */
$config['expire'] = 0;

/**
 * Set to true, to only allow logging in through HTTPS, or false to allow
 * logging in through HTTP.
 */
$config['secure'] = true;

/**
 * Enable this option to disable the cookie from being accessed when using a
 * secure protocol. This option is only available in PHP 5.2 and above.
 */
$config['httponly'] = true;
