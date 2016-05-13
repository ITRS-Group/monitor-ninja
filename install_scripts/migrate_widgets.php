#!/usr/bin/env php
<?php

if(PHP_SAPI !== 'cli') {
	die("This script should never be run from a browser, execute it from commmand-line instead.");
}

/**
 * Used to migrate widgets from Ninja_Widget_Model to Dashboard_Widget_model.
 *
 * This upgrade is needed from 7.2 to later versions.
 *
 * Due to upgrade policies, this script can be removed in Monitor 8 and later
 */

if(!class_exists('Kohana')) {
	define('SKIP_KOHANA', true);
	require_once(realpath(__DIR__.'/../index.php'));
}
op5auth::instance()->force_user(new User_AlwaysAuth_Model());

/* Fetch from pool. so we don't introduce auth filters in migration */
$ninja_widgets = Ninja_WidgetPool_Model::pool()->it(new LivestatusFilterAnd(), false);

$widgets_per_user = array();

foreach($ninja_widgets as $ninja_widget) {
	/* @var $ninja_widget Ninja_Widget_Model */
	$username = $ninja_widget->get_username();
	if($username === '') {
		/* NULL username means available widgets in really old tac */
		continue;
	}
	if(!isset($widgets_per_user[$username])) {
		$widgets_per_user[$username] = array();
	}
	$widgets_per_user[$username][] = $ninja_widget;
}

foreach($widgets_per_user as $username => $ninja_widgets) {
	$dashboard = new Dashboard_Model();
	$dashboard->set_username($username);
	$dashboard->set_name("Dashboard for $username");
	$dashboard->set_layout('3,2,1');
	$dashboard->save();

	$order_setting = SettingPool_Model::all()
		->reduce_by('username', $username, '=')
		->reduce_by('type', 'widget_order', '=')
		->reduce_by('page', 'tac/index', '=')
		->one();

	/* Default layout has 6 cells */
	$widget_place = array();

	if($order_setting) {

		/* @var $order Setting_Model */
		$placeholders = explode('|', $order_setting->get_setting());
		$pos_data = array_map(
			function ($ph) {
				$values = explode('=', $ph);
				if ($values[1] == '') return array();
				return explode(',', $values[1]);
			},
			$placeholders
		);

		foreach($pos_data as $c => $widget_tags) {
			foreach($widget_tags as $p => $tag) {
				$widget_place[$tag] = array('c' => $c, 'p' => $p);
			}
		}
	}

	foreach($ninja_widgets as $ninja_widget) {
		/* @var $ninja_widget Ninja_Widget_Model */
		$widget = new Dashboard_Widget_Model();
		$widget->set_dashboard_id($dashboard->get_id());
		$widget->set_name($ninja_widget->get_name());

		$setting = $ninja_widget->get_setting();
		$setting['title'] = $ninja_widget->get_friendly_name();
		$widget->set_setting($setting);

		$tag = 'widget-' . $ninja_widget->get_widget_id();
		if(isset($widget_place[$tag])) {
			$widget->set_position($widget_place[$tag]);
		}


		$widget->save();
	}
}

echo "\n";