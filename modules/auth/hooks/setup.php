<?php

Event::add('ninja.setup.user', function () {
	$data = Event::$data;

	$logger = op5log::instance('ninja');
	$logger->log('notice', "Starting initial setup");

	/**
	 * Some validation beforehand
	 */
	if ($data['user']['password'] !== $data['user']['password-repeat'])
		throw new Exception('Passwords do not match');
	if (strlen($data['user']['password']) === 0)
		throw new Exception('You need to set a password');

	/**
	 * The User itself may be created via ORM which is nice.
	 */
	$logger->log('notice', "Creating administrative user");

	$user = new User_Model();
	$user->set_username($data['user']['username']);
	$user->set_realname($data['user']['realname']);
	$user->set_password($data['user']['password']);
	$user->set_groups(array('admins'));
	$user->set_modules(array('Default'));
	$user->save();

	$logger->log('notice', "Created administrative user");

});
