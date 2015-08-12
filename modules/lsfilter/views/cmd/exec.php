<?php
if(isset($error)) {
	echo '<div class="alert error">'.sprintf(_('There was an error submitting your command to %s.'), Kohana::config('config.product_name'));
	if (!empty($error)) {
		echo '<br /><br />'._('ERROR').': '.$error;
	}
	echo "</div>\n";
}

if(isset($results)) {
	$footer = '';
	foreach($results as $view) {
		/* @var $view View */
		$view->footer = $footer;
		$view->render(true);
		$footer = $view->footer;
	}
	echo $footer;
}
