<?php

$module_path = Kohana::$module_path;
Event::add("system.post_controller_constructor", function () use ($module_path) {
	$controller = Event::$data;
	$controller->template->js[] = $module_path.'/media/js/grafana.js';
	$controller->template->css[] = $module_path . '/media/css/grafana.css';
});
