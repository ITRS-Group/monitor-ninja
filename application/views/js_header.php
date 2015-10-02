<?php defined('SYSPATH') OR die('No direct access allowed.');

$basepath = url::base();

if(empty($js))
	$js = array();

foreach ($js as $js_file) {
	if(!preg_match('/^[a-z]+:\/\//', $js_file)) {
		$js_file = $basepath . $js_file;
	}
	$path = ninja::add_version_to_uri($js_file);
	echo "<script type=\"text/javascript\" src=\"".html::specialchars($path)."\"></script>\n";
}
