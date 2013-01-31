<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"></div>
<div id="progress"></div>
<?php
	echo isset($error) ? $error : '';
	echo !empty($header) ? $header : '';
	echo !empty($report_options) ? $report_options : '';
	echo !empty($content) ? $content : '';
?>
