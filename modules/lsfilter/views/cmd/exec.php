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
	$first = true;
	foreach($results as $view) {
		/* @var $view View */
		$view->header = '';

		$view->footer = $footer;
		$content = $view->render(false);
		$footer = $view->footer;

		if($first) {
			echo $view->header;
			$first = false;
		}
		echo $content;
	}
	echo $footer;
}
