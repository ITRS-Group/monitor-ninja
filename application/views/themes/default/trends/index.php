<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div id="response"></div>
<div id="progress"></div>
<div class="report-page">
<?php
	echo isset($error) ? $error : '';
	echo !empty($header) ? $header : '';
	echo !empty($report_options) ? $report_options : '';

	echo !empty($content) ? $content : '';
?>
</div>