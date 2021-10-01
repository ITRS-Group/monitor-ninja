<?php


/**
 * User model
 *
 * @todo: documentation
 */
class User_Model extends BaseUser_Model implements op5MayI_Actor {

	/**
	 * For backward compatible reasons
	 */
	protected $custom_properties = array();

	protected $export = array(
		'username',
		'realname',
		'email',
		'modules',
		'auth_data',
		'auth_method',
		'groups'
	);

	/**
	 * Constructs a new user, if data is provided the
	 * user will be populated with that data.
	 *
	 * @param $data array User data
	 */
	public function __construct (array $data = array()) {
		foreach ($data as $key => $value) {
			if ($key === 'key') {
				// 'key' is a special property, it denotes the
				// "primary key" of this object, in this class
				// it represents 'username'
				continue;
			}
			$setter = "set_" . $key;
			if (method_exists($this, $setter)) {
				$this->$setter($value);
			} else {
				flag::deprecated(
					__METHOD__,
					sprintf(
						"Backwards-compatibility after op5user => User_Model, trying to ".
						"set '%s' to '%s'",
						$key,
						var_export($value, true)
					)
				);
				$this->custom_properties[$key] = $value;

				// make sure a call to export() includes the newly
				// attached key
				$this->export[] = $key;
			}
		}
	}

	/**
	 * Backwards compatibility with now removed op5user class
	 *
	 * @param $property
	 * @return mixed
	 */
	public function __get($property) {
		flag::deprecated(__METHOD__, "Backwards-compatibility after op5user => User_Model");
		$method = 'get_'.$property;
		if(method_exists($this, $method)) {
			return $this->$method();
		}
		if(array_key_exists($property, $this->custom_properties)) {
			return $this->custom_properties[$property];
		}
		return null;
	}

	/**
	 * Backwards compatibility with now removed op5user class
	 *
	 * @param $property
	 * @param $value
	 */
	public function __set($property, $value) {
		flag::deprecated(__METHOD__, "Backwards-compatibility after op5user => User_Model");
		$setter = "set_" . $property;
		if (method_exists($this, $setter)) {
			$this->$setter($value);
		} else {
			$this->custom_properties[$property] = $value;

			// make sure a call to export() includes the newly
			// attached property
			$this->export[] = $property;
		}
	}

	/**
	 * @param $property
	 * @return boolean
	 */
	public function __isset($property) {
		if(isset($this->custom_properties[$property])) {
			return true;
		}
		return $this->$property !== NULL;
	}

	/**
	 * Returns a display name of the user, i.e. selects realname if set,
	 * otherwise the username
	 *
	 * @return string The display name
	 */
	public function get_display_name () {
		return (strlen($this->get_realname()) === 0) ? $this->get_username() : $this->get_realname();
	}

	public function set_password($value) {
		if (strlen($value) > 0) {
			parent::set_password(password_hash($value, PASSWORD_DEFAULT));
		}
		/* TODO: Which hashing algorithm? crypt is the only simple one
		 * available which has salt... */
		parent::set_password_algo('crypt');
	}

	/**
	 * Returns a boolean whether this user has logged in to the product
	 * before
	 *
	 * @return bool
	 */
	public function has_logged_in () {
		return ($this->get_last_login_time() !== null);
	}

	/**
	 * Returns the timestamp of this users last login or null if no time was
	 * found
	 *
	 * @return mixed
	 */
	public function get_last_login_time () {

		$login_time = SettingPool_Model::all()
			->reduce_by('username', $this->get_username(), '=')
			->reduce_by('type', 'login_time', '=')
			->one();

		if ($login_time) {
			return (int) $login_time->get_setting();
		} else return null;

	}

	/**
	 * Return a regexp for matching a quark string for the current users access
	 *
	 * @return string
	 */
	public function get_permission_regexp() {
		$quarks = array();
		$quarks[] = $this->get_permission_quark();
		foreach($this->get_usergroups_set() as $group) {
			$quarks[] = $group->get_permission_quark();
		}
		return ',(' . implode('|', $quarks) . '),';
	}

	protected function validate () {

		$set = AuthModulePool_Model::all();

		if (strlen($this->get_username()) === 0) {
			throw new ORMException('User requires a username to save');
		}

		foreach($this->get_modules() as $modulename) {
			$module = $set->reduce_by('modulename', $modulename, '=')->one();
			$module->validate_user($this);
		}

		return true;

	}

	/**
         * Returns if a user is authorized for a certain authorization point
         *
         * @param $auth_point string
         * @return boolean true if user has access to that authorization point
         */
	public function authorized_for($auth_point) {
		$auth_data = $this->get_auth_data();
		return isset($auth_data[$auth_point])
			? $auth_data[$auth_point]
			: false;
        }

	/**
         * Test if user is authorized for viewing a certain object
         *
         * @param $object_definition string
         *              object name, or array of names defining a "path"
         * @param $object_type string
         *              object type (host/service)
         * @param $case_sensitivity boolean
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
                                        'AuthUser: ' . $this->get_username()), array ('name'));
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
                                                 $object_definition[1],'AuthUser: ' . $this->get_username()),
                                array ('description'));
                        if ($count > 0) {
                                $access = true;
                        }
                        break;
                }
                return $access;
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
                        array('Filter: members >= ' . $this->username), array ('name'));
                $result = array();
                foreach ($objects as $row) {
                        $result[] = $row[0];
                }
                return $result;
        }

        /**
         * Get a set of all user groups for this user
         *
         * @return UserGroupSet_Model
         */
        public function get_usergroups_set() {
			$groups = UserGroupPool_Model::none();
			foreach($this->get_groups() as $group) {
				$groups = $groups->union(UserGroupPool_Model::set_by_key($group));
			}
			return $groups;
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
                        'username' => $this->get_username(),
                        'realname' => $this->get_realname(),
                        'email' => $this->get_email(),
                        'authorized' => $this->get_auth_data(),
                        'groups' => $this->get_groups()
                );
        }


}
