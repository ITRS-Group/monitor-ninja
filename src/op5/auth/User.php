<?php
require_once (__DIR__ . '/../mayi.php');

class op5User implements op5MayI_Actor {

	/**
	 * Holds user definitions
	 *
	 * @var $fields array
	 *
	 */
	public $fields = array ('username' => false,'realname' => false,
		'email' => false, 'auth_data' => array());

	/**
	 * Overload set
	 *
	 * @param $key string
	 * @param $value mixed
	 * @return void
	 *
	 */
	public function __set($key, $value) {
		$this->fields[$key] = $value;
	}

	/**
	 * Overload get
	 *
	 * @param $key string
	 * @return mixed
	 *
	 */
	public function __get($key) {
		return $this->fields[$key];
	}

	/**
	 * Overload isset
	 *
	 * @param $key string
	 * @return boolean
	 *
	 */
	public function __isset($key) {
		return isset($this->fields[$key]);
	}

	/**
	 * Overload unset
	 *
	 * @param $key string
	 * @return void
	 *
	 */
	public function __unset($key) {
		unset($this->fields[$key]);
	}

	/**
	 * Contruct
	 *
	 * @param $fields array
	 * @return void
	 *
	 */
	public function __construct($fields) {
		$this->fields = array_merge($this->fields, $fields);
	}

	/**
	 * Returns if a user is authorized for a certain authorization point
	 *
	 * @param $auth_point string
	 * @return boolean true if user has access to that authorization point
	 */
	public function authorized_for($auth_point) {
		return isset($this->auth_data[$auth_point]) ? $this->auth_data[$auth_point] : false;
	}

	/**
	 * Test if authorized for viewing a certain object
	 * ��
	 *
	 * @param $object_definition string
	 *        	object name, or array of names defining a "path"
	 * @param $object_type string
	 *        	object type (host/service)
	 * @param $case_insensitivity boolean
	 */
	public function authorized_for_object($object_type, $object_definition,
		$case_sensitivity = true) {
		$ls = op5livestatus::instance();
		$lseq = $case_sensitivity ? '=' : '=~';
		$access = false;

		switch ($object_type) {
		case 'host':
		case 'hosts':
		case 'hostgroup':
		case 'hostgroups':
		case 'servicegroup':
		case 'servicegroups':
			list ($columns, $objects, $count) = $ls->query($object_type,
				array ('Filter: name ' . $lseq . ' ' . $object_definition,
					'AuthUser: ' . $this->username), array ('name'));
			if ($count > 0) {
				$access = true;
			}
			break;
		case 'service':
		case 'services':
			list ($columns, $objects, $count) = $ls->query('services',
				array ('Filter: host_name ' . $lseq . ' ' .
					 $object_definition[0],
						'Filter: description ' . $lseq . ' ' .
						 $object_definition[1],'AuthUser: ' . $this->username),
				array ('description'));
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
	 * @param $password string
	 * @return boolean
	 */
	public function change_password($password) {
		return false;
	}

	/**
	 * Returns true if logged in
	 *
	 * @return boolean always true (normal users are logged in, notauth
	 *         overrides)
	 */
	public function logged_in() {
		return true;
	}

	/**
	 * List all contact groups I am a member of
	 *
	 * TODO: Deprecate? (this method is called from Nagvis)
	 *
	 * @return array array of groups
	 */
	public function get_contact_groups() {
		$ls = op5livestatus::instance();
		list ($columns, $objects, $count) = $ls->query('contactgroups',
			array ('Filter: members >= ' . $this->username), array ('name'));
		$result = array ();
		foreach ($objects as $row) {
			$result[] = $row[0];
		}
		return $result;
	}

	/**
	 * Return information about the user, to be used as an actor in the MayI
	 * interface
	 *
	 * @see op5MayI_Actor::getActorInfo()
	 */
	public function getActorInfo() {
		return array(
			'type' => 'user',
			'authenticated' => $this->logged_in(),
			'name' => $this->username,
			'realname' => $this->realname,
			'email' => $this->email,
			'authorized' => $this->auth_data,
			'groups' => isset($this->groups) ? $this->groups : array()
		);
	}
} // End Auth User Model
