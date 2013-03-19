<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Should this function be enabled or not?
*/
$config['enable'] = false;

/**
 * Path to where you will store your local customlogo
 * this should be relative to your icons folder for your view
*/
$config['path'] = 'custom_logo/';

/**
 * Pattern to match against, eg if you name a contact
 * corp-username the pattern should look something like
 * $config['pattern'] = '/^(.*)-.*$)/';
 */
$config['pattern'] = '/^(.*)-.*$/';

/**
 * The default icon to be used if the "custom_logo" is missing
 * Leave empty if you don't want to display a custom logo for
 * those who don't have a matching image file
 */
$config['default_icon'] = 'icon.png';
