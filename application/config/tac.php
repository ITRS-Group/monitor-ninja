<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package Session
 *
 * A list of column counts in the tac
 */
$config['column_count'] = '3,2,1';

$config ['default'] = array (
		'dashboard' => array (
				'name' => 'Dashboard',
				'layout' => '3,2,1'
		),
		'widgets' => array (
				array (
						'name' => 'netw_health',
						'setting' => array (),
						'position' => array (
								'c' => 2,
								'p' => 0
						)
				),
				array (
						'name' => 'bignumber',
						'setting' => array (
								'title' => 'Services ok',
								'refresh_interval' => '60',
								'main_filter_id' => -100,
								'selection_filter_id' => -50,
								'display_type' => 'number_of_total',
								'threshold_type' => 'no_thresholds',
								'threshold_warn' => '0',
								'threshold_crit' => '0'
						),
						'position' => array (
								'c' => 1,
								'p' => 0
						)
				),
				array (
						'name' => 'bignumber',
						'setting' => array (
								'title' => 'Hosts ok',
								'refresh_interval' => '60',
								'main_filter_id' => -200,
								'selection_filter_id' => -150,
								'display_type' => 'number_of_total',
								'threshold_type' => 'no_thresholds',
								'threshold_warn' => '0',
								'threshold_crit' => '0'
						),
						'position' => array (
								'c' => 0,
								'p' => 0
						)
				),
				array (
						'name' => 'listview',
						'setting' => array (),
						'position' => array (
								'c' => 3,
								'p' => 0
						)
				),
				array (
						'name' => 'listview',
						'setting' => array (
								'query' => '[services] all',
								'columns' => 'all',
								'limit' => '20',
								'order' => ''
						),
						'position' => array (
								'c' => 4,
								'p' => 0
						)
				)
		)
);
