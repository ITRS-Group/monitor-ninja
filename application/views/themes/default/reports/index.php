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
	echo !empty($trends_graph) ? $trends_graph : '';
	echo !empty($content) ? $content : '';
	echo !empty($svc_content) ? $svc_content : '';
	echo isset($pie) ? $pie : '';
	echo !empty($log_content) ? $log_content : '';
?>
</div>