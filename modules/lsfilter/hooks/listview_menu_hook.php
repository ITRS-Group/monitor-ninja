<?php
Event::add ( 'ninja.menu.setup', function () {

	$menu = Event::$data;
	$mayi = op5MayI::instance ();
	$max_filters = 6;

	$section = $menu->get ( 'Monitor' );
	$menu->set ( 'Manage.Manage filters', listview::querylink ( '[saved_filters] all' ), 3, 'icon-16 x16-eventlog' );
} );
