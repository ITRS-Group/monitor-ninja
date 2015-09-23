<?php defined('SYSPATH') OR die('No direct access allowed.');

$basepath = url::base();

if(empty($js))
	$js = array();

foreach ($js as $js_file) {
	$path = ninja::add_version_to_uri($basepath.$js_file);
	echo "<script type=\"text/javascript\" src=\"".html::specialchars($path)."\"></script>\n";
}
