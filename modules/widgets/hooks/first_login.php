<?php

Event::add('ninja.first_login', function () {

	$user = op5auth::instance()->get_user();

	/* Create default dashboard on first login */
	$dashboard = new Dashboard_Model();
	$dashboard->set_username($user->get_username());
	$dashboard->import_array(Kohana::config('tac.default'));
	$dashboard->set_name('Dashboard for ' . $user->get_username());
	$dashboard->save();

});
