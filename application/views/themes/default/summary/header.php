<?php defined('SYSPATH') OR die('No direct access allowed.');

if ($options['standardreport']) {
	echo '<div id="report_mode_form" style="display: none"><input type="radio" value="standard" checked="checked" /></div>';
} else {
	echo '<div id="report_mode_form" style="display: none"><input type="radio" value="custom" checked="checked" /></div>';
}

echo $standard_header;
