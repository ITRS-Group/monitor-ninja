<?php

class op5User {

	/* Only to be accessed from op5Auth */
	public $fields = array(
		'username'  => false,
		'realname'  => false,
		'email'     => false,
	);

	public function __set($key, $value)
	{
		$this->fields[$key] = $value;
	}

	public function __get($key)
	{
		return $this->fields[$key];
	}
	
	public function __isset($key)
	{
		return isset( $this->fields[$key] );
	}
	
	public function __unset($key)
	{
		unset( $this->fields[$key] );
	}


	public function __construct( $fields ) {
		$this->fields    = $fields;
	}
	
	/**
	 * Returns if a user is authorized for a certain authorization point
	 *
	 * @param 	string 		authorization point
	 * @return 	boolean 	true if user has access to that authorization point
	 */
	public function authorized_for($auth_point)
	{
		return isset( $this->auth_data[ $auth_point ] ) ? $this->auth_data[ $auth_point ] : false;
	}

	/**
	 * Test if authorized for viewing a certain object
	 *»
	 * @param $authorization_point string Name of authorization point
	 * @param $object_definition   string object name, or array of names defining a "path"
	 * @param $object_type         string object type (host/service)
	 */
	public function authorized_for_object($object_type, $object_definition, $case_sensitivity=true)
	{
		$ls = op5livestatus::instance();
		$lseq = $case_sensitivity?'=':'=~';
		$access = false;

		switch($object_type) {
			case 'hosts':
			case 'hostgroups':
			case 'servicegroups':
				list($columns,$objects,$count) = $ls->query($object_type, array(
						'Filter: name '.$lseq.' '.$object_definition,
						'AuthUser: ' . $this->username
					), array('name'));
				if($count > 0) {
					$access = true;
				}
				break;
			case 'services':
				list($columns,$objects,$count) = $ls->query('services', array(
						'Filter: host_name '.$lseq.' '.$object_definition[0],
						'Filter: description '.$lseq.' '.$object_definition[1],
						'AuthUser: ' . $this->username
					), array('description'));
				if ($count > 0) {
					$access = true;
				}
				break;
		}
		return $access;
	}

	/**
	 * Updates the password of the user.
	 *
	 * @param  string    new password
	 * @return boolean
	 */
	public function change_password( $password )
	{
		return false;
	}

	/**
	 * Returns true if logged in
	 *
	 * @return  boolean   always true (normal users are logged in, notauth overrides)
	 */
	public function logged_in()
	{
		return true;
	}

	/**
	 * List all contact groups I am a member of
	 *
	 * TODO: Deprecate? (this method is called from Nagvis)
	 *
	 * @return array array of groups
	 */
	public function get_contact_groups()
	{
		$ls = op5livestatus::instance();
		list($columns, $objects, $count) = $ls->query('contactgroups', array(
				'Filter: members >= ' . $this->username
			),
			array('name')
		);
		$result = array();
		foreach ($objects as $row) {
		        $result[] = $row[0];
		}
		return $result;
	}
} // End Auth User Model
