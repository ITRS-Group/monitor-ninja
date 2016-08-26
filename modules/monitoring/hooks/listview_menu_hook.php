<?php
function listview_menu_label($name) {
	return ucfirst ( preg_replace ( "/\_/", " ", $name ) );
}

Event::add ( 'ninja.menu.setup', function () {

	$auth = Auth::instance ();
	$menu = Event::$data;
	$mayi = op5MayI::instance ();

	if (!is_object($menu) || !($menu instanceof Menu_Model)) {
		return;
	}

	if (op5MayI::instance()->run('ninja.configuration:read')) {

		$menu->set ( 'Manage', null, 4, 'icon-16 x16-configuration', array (
				'style' => 'margin-top: 8px'
		) );

		$menu->set ( 'Manage.View active config', 'config', 1, 'icon-16 x16-viewconfig' );
		$menu->set ( 'Manage.Backup/Restore', 'backup', 2, 'icon-16 x16-backup' );

		$menu->set ( 'Manage.Scheduling queue', 'extinfo/scheduling_queue', 4, 'icon-16 x16-schedulingqueue' );

		$menu->set ( 'Manage.Performance information', 'extinfo/performance', 5, 'icon-16 x16-info' );
		$menu->set ( 'Manage.Process information', 'extinfo/show_process_info', 6, 'icon-16 x16-info' );
	}

	$max_filters = 6;

	$menu->set ( 'Manage.Manage filters', listview::querylink ( '[saved_filters] all' ), 3, 'icon-16 x16-eventlog' );
	$menu->set ( 'Monitor.Network Outages', 'outages', null, 'icon-16 x16-outages' );

	$tables = array (
			'hosts' => array (
					'pool' => 'HostPool_Model',
					'icon' => 'icon-16 x16-host'
			),
			'services' => array (
					'pool' => 'ServicePool_Model',
					'icon' => 'icon-16 x16-service'
			),
			'hostgroups' => array (
					'pool' => 'HostgroupPool_Model',
					'icon' => 'icon-16 x16-hostgroup'
			),
			'servicegroups' => array (
					'pool' => 'ServicegroupPool_Model',
					'icon' => 'icon-16 x16-servicegroup'
			)
	);

	$saved = array ();
	$set = SavedFilterPool_Model::all ();

	$it = $set->it(false, array('filter_name'));

	foreach ( $it as $value ) {
		$table = $value->get_filter_table ();
		if (! isset ( $saved [$table] ))
			$saved [$table] = array ();
		$saved [$table] [] = $value;
	}

	foreach ( $tables as $table => $def ) {

		$singular = preg_replace ( '/s$/', '', $table );
		$pool = $def ['pool'];

		$resource = $pool::all ()->mayi_resource ();
		$key = "Monitor." . listview_menu_label ( $table );

		if ($mayi->run($resource . ':read.list')) {

			$menu->set($key, null, null, sprintf($def['icon']));

			$menu->set($key . '.All ' . listview_menu_label ( $table ), listview::querylink(sprintf('[%s] all', $table)), null, $def['icon']);

			$count = 0;
			if (isset ( $saved [$table] )) {
				foreach ( $saved [$table] as $object ) {

					$count ++;

					if ($count > $max_filters) {
						$menu->set ( $key . '.All filters for ' . preg_replace ( '/\./', '&period;', $table ), listview::querylink ( sprintf ( '[saved_filters] filter_table = "%s"', $table ) ), null, sprintf ( 'icon-16 x16-%s', 'filter' ) );
						break;
					}

					$menu->set ( $key . '.' . $object->get_filter_name (), listview::querylink ( $object->get_filter () ), null, $def['icon']);
				}
			}

		}
	}

	$menu->set ( 'Monitor.Downtimes', null, null, 'icon-16 x16-downtime' );
	$menu->set ( 'Monitor.Downtimes.All Downtimes', listview::querylink ( '[downtimes] all' ), 0, 'icon-16 x16-downtime' );
	$menu->set ( 'Monitor.Downtimes.Recurring Downtimes', listview::querylink ( '[recurring_downtimes] all' ), 1, 'icon-16 x16-recurring-downtime' );

	$icon = 'icon-16 x16-notification';
	$menu->set ( 'Report.Notifications', null, null, $icon );

	$count = 0;
	$table = "notifications";

	$menu->set ( 'Report.Notifications.All Notifications', listview::querylink ( '[notifications] all' ), 0, $icon );

	if (isset ( $saved [$table] )) {
		foreach ( $saved [$table] as $object ) {

			$count ++;

			if ($count > $max_filters) {
				$menu->set ( 'Report.Notifications.All filters for ' . preg_replace ( '/\./', '&period;', $table ), listview::querylink ( sprintf ( '[saved_filters] filter_table = "%s"', $table ) ), null, sprintf ( 'icon-16 x16-%s', 'filter' ) );
				break;
			}

			$menu->set ( 'Report.Notifications.' . $object->get_filter_name (), listview::querylink ( $object->get_filter () ), null, sprintf ( 'icon-16 x16-%s', $icon ) );
		}
	}

	$menu->set ( 'Monitor.NagVis', 'nagvis', null, 'icon-16 x16-nagvis' );

} );
