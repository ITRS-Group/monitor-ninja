<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['default'] = array (
	'dashboard' => array (
			'name' => 'Dashboard',
			'layout' => '1,3,2'
	),
	'widgets' => array (
		array (
			'name' => 'bignumber',
			'setting' => array (
				'title' => 'Hosts up',
				'refresh_interval' => '60',
				'main_filter_id' => '-200',
				'selection_filter_id' => '-150',
				'display_type' => 'number_of_total',
				'toggle_me' => '1',
				'threshold_type' => 'less_than',
				'threshold_onoff' => '1',
				'threshold_warn' => 100,
				'threshold_crit' => 95,
			),
			'position' => array ( 'c' => 1, 'p' => 0 )
		),
		array (
			'name' => 'bignumber',
			'setting' => array (
				'title' => 'Unhandled host problems',
				'refresh_interval' => '60',
				'main_filter_id' => '-200',
				'selection_filter_id' => '-151',
				'display_type' => 'number_only',
				'toggle_me' => '1',
				'threshold_type' => 'greater_than',
				'threshold_onoff' => '1',
				'threshold_warn' => 5,
				'threshold_crit' => 10,
			),
			'position' => array ( 'c' => 2, 'p' => 0 )
		),
		array (
			'name' => 'bignumber',
			'setting' => array (
				'title' => 'Unhandled service problems',
				'refresh_interval' => '60',
				'main_filter_id' => '-100',
				'selection_filter_id' => '-51',
				'display_type' => 'number_only',
				'toggle_me' => '1',
				'threshold_type' => 'greater_than',
				'threshold_onoff' => '1',
				'threshold_warn' => 5,
				'threshold_crit' => 10,
			),
			'position' => array ( 'c' => 3, 'p' => 0 )
		),
		array (
			'name' => 'state_summary',
			'setting' => array (
					'title' => 'Hosts',
					'filter_id' => -200
			),
			'position' => array ( 'c' => 4, 'p' => 0 )
		),
		array (
			'name' => 'state_summary',
			'setting' => array (
					'title' => 'Services',
					'filter_id' => -100
			),
			'position' => array ( 'c' => 5, 'p' => 0 )
		)
	)
);

/* Only add getting started widget if available */
$widgets_available = Dashboard_WidgetPool_Model::get_available_widgets();
if(isset($widgets_available['gettingstarted'])) {
	$config['default']['widgets'][] =  array (
			'name' => 'gettingstarted',
			'setting' => array (),
			'position' => array ( 'c' => 0, 'p' => 0 )
		);
}