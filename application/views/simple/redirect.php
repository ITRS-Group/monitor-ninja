<?php defined('SYSPATH') OR die('No direct access allowed.');

/*
 * Redirect to a page.
 *
 * Two variables:
 * - $url = url to page to redirect to
 * - $target = where the url is related to (optional default: controller)
 * -- "controller" => $url relative to index.php/
 * -- other: To be implemented
 */

if(!isset($target))
	$target = 'controller';

switch($target) {
	case 'controller':
		$url = url::base(true, ((empty($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] === 'off') ? 'http' : 'https')).$url;
		break;
}
header('Location: '.$url);

echo "<a href=\"$url\">$url</a>";
