<?php defined('SYSPATH') OR die('No direct access allowed.');

$basepath = url::base();

if(empty($js))
	$js = array();

foreach ($js as $js_file) {
	if (preg_match('/^\/monitor\/op5\/nacoma/', $js_file)) {
		echo "<script type=\"text/javascript\" src=\"".html::specialchars($js_file)."\"></script>\n";
		continue;
	}
	if(!preg_match('/^[a-z]+:\/\//', $js_file)) {
		$js_file = $basepath . $js_file;
	}
	echo "<script type=\"text/javascript\" src=\"".html::specialchars($js_file)."\"></script>\n";
}
