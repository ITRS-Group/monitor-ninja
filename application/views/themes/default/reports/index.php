<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?><br />
<br />
<br />

<div id="response"></div>
<div id="progress"></div>
<div class="report-page">
<?php
if (isset($error))
	echo $error;

echo !empty($header) ? $header : '';
echo !empty($report_options) ? $report_options : '';
echo !empty($svc_content) ? $svc_content : '';
echo !empty($content) ? $content : '';
echo isset($pie) ? $pie : '';
	?>
<?php
	if (!empty($log_content)) {
		echo '<div id="log_messages">'."\n";
		echo $log_content;
		echo '</div>'."\n";
	}
?>
</div>