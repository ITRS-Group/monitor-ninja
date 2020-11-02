<?php defined('SYSPATH') OR die('No direct access allowed.');

$basepath = url::base();
if(empty($js)) $js = array();

$js = array_map(function ($file) use ($basepath) {
	if (preg_match('/^\/monitor\/op5\/nacoma/', $file)) {
		return $file;
	}
	if(!preg_match('/^[a-z]+:\/\//', $file)) {
		return $basepath . $file;
	}
	return $file;
}, $js);

sort($js);
foreach ($js as $js_file) {
	echo "<script type=\"text/javascript\" src=\"".html::specialchars($js_file)."\"></script>\n";
}
