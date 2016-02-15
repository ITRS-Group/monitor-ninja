<?php

$tables = array(
	'users' => array(
		'class' => 'User',
		'source' => 'YAML',
		'table' => 'auth_users',
		'key' => array('username'),
		'writable' => true,
		'default_sort' => array('username desc'),
		'structure' => array(
			'username' => 'string',
			'realname' => 'string',
			'password' => 'password',
			'email' => 'string',
			'auth_method' => 'string',
			'auth_driver' => 'string',
			'auth_data' => 'list',
			'modules' => 'list',
			'groups' => 'list',
			'password_algo' => 'string'
		),
		'renderable' => array(
			'username' => array(
				'type' => 'text',
				'label' => 'Username'
			),
			'realname' => array(
				'type' => 'text',
				'label' => 'Realname'
			),
			'password' => array(
				'type' => 'password',
				'label' => 'Password'
			),
			'modules' => array(
				'type' => 'select',
				'filters' => 'authmodule',
				'label' => 'Auth. Modules'
			),
			'groups' => array(
				'type' => 'select',
				'filters' => 'usergroup',
				'label' => 'Groups'
			)
		)
	),
	'usergroups' => array(
		'class' => 'UserGroup',
		'source' => 'YAML',
		'table' => 'auth_groups',
		'key' => array('groupname'),
		'default_sort' => array('groupname desc'),
		'structure' => array(
			'groupname' => 'string',
			'rights' => 'list'
		),
	),
	'authmodules' => array(
		'class' => 'AuthModule',
		'source' => 'YAML',
		'table' => 'auth',
		'writable' => true,
		'key' => array('modulename'),
		'default_sort' => array('modulename desc'),
		'structure' => array(
			'modulename' => 'string',
			'properties' => 'dict'
		)
	),
);
