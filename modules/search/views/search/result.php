<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($widgets)) {
	foreach ($widgets as $definition) {
		$widget = $definition["widget"];
		echo '<h2> &nbsp; ' . $definition['title'] . '</h2><hr />';
		echo $widget->render('index', false);
	}
}