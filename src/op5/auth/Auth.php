<?php
require_once (__DIR__ . '/../config.php');
require_once (__DIR__ . '/../mayi.php');
require_once (__DIR__ . '/Authorization.php');
require_once (__DIR__ . '/../log.php');
require_once (__DIR__ . '/../livestatus.php');
require_once (__DIR__ . '/../objstore.php');

/**
 * User authentication and authorization library.
 *
 * It is possible to add the user itself as an actor to mayi, but since the
 * active user might change during the lifecycle of an execution, but not the
 * authorization, it's better to keep the autorization module itself as an
 * actor, just passing through the current users information upon request. That
 * makes it possible to register the autorization actor, then just access the
 * information from the mayi constraints afterwards.
 *
 * @package Auth
 */
class op5auth implements op5MayI_Actor {

	/**
	 * Default configuration is specified here. Parameters may be overwritten
	 * from config
	 *
	 * @var array
	 */
	private $config = array(
		'enable_auto_login' => false,
		'apc_enabled' => false,
		'apc_ttl' => 60,
		'apc_store_prefix' => 'op5_auth_',
		'session_key' => false
	);

	/**
	 * A list of auth modules config's until the module is loaded, then the
	 * config is replaced with the module.
	 *
	 * @var AuthModuleSet_Model
	 */
	private $auth_modules = null;

	/**
	 * A list of drivers indexed by auth module name
	 *
	 * @var array Of op5AuthDriver's
	 */
	private $drivers = array();

	/**
	 * The user object currently authenticated
	 *
	 * @var User_Model
	 */
	private $user = null;

	/**
	 * Returns an instance of op5auth
	 *
	 * @param $config array
	 * @param $driver_config array
	 * @return void
	 */
	static public function instance($config = false, $driver_config = false) {
		return op5objstore::instance()->obj_instance_callback(
			__CLASS__,
			function () use($config, $driver_config) {
				return new op5auth($config, $driver_config);
			}
		);
	}

	/**
	 * Just so we dont break compatibility.
	 * DONT USE!
	 *
	 * @deprecated
	 * @param $config array
	 * @param $driver_config array
	 * @return void
	 */
	static public function factory($config = false, $driver_config = false) {
		op5objstore::instance()->unload(__CLASS__);
		return self::instance($config, $driver_config);
	}

	/**
	 * Creates an op5Auth instance.
	 *
	 * @throws Exception Essential configuration is missing
	 * @param $config array The array overrides the values read from common configuration
	 * @param $driver_config array
	 */
	public function __construct($config = false) {

		$this->log = op5Log::instance('auth');
		$authconf = op5config::instance()->getConfig('auth');

		if (!isset($authconf['common']))
			throw new Exception('section "common" not found in auth.yml');

		$common = $authconf['common'];
		$this->config = array_merge($this->config, $common);

		if (is_array($config)) {
			$this->config = array_merge($this->config, $config);
		}

		/*
		 * Fetch list of auth modules, only blocks containing
		 * variable "driver" is treated as a auth modules the
		 * $this->auth_modules array contains configuration of
		 * auth modules until loaded, then the configuration is
		 * changed to the actual auth module object, to enable
		 * lazy loading.
		 */
		$this->auth_modules = AuthModulePool_Model::all();

		if (count($this->auth_modules) === 0) {
			throw new Exception('No authentication driver specified');
		}

		if (count($this->auth_modules) == 1) {
			$this->config['default_auth'] = $this->auth_modules->one()->get_modulename();
		}
	}

	/**
	 * Get a list of authentication modules for a given driver.
	 *
	 * @param $drivername string Name of the driver
	 * @return array List of AuthModule_Model's
	 */
	public function get_modules_by_driver($drivername) {
		$modules = array();
		foreach($this->auth_modules as $module) {
			$properties = $module->get_properties();
			if (isset($properties['driver']) && $properties['driver'] === $drivername) {
				$modules[] = $module;
			}
		}
		return $modules;
	}

	/**
	 * Check if there is an active user that is logged authenticated.
	 *
	 * @return boolean
	 */
	public function logged_in() {
		return $this->get_user()->logged_in();
	}

	/**
	 * Returns the users group memberships
	 *
	 * @return array
	 */
	public function get_groups() {
		if (!$this->logged_in()) return array();
		return $this->get_user()->get_groups();
	}

	/**
	 * Returns the currently logged in user, or NoAuth user.
	 *
	 * @return mixed
	 */
	public function get_user() {

		if ($this->user === null && $this->config['session_key'])
			$this->user = $this->session_fetch($this->config['session_key']);

		/*
		 * This is needed for Apache auth, which doesn't go through login method
		 * But let every auth driver try to auto_login, just to make generic.
		 * FIXME: Don't iterate over all... how and still be generic?
		 */
		if ($this->config['enable_auto_login'] && $this->user === null) {
			foreach ($this->auth_modules as $module)  {

				$driver = $this->get_auth_driver($module->get_modulename());
				$user = $driver->auto_login();

				if ($user) {
					/* Postprocess login */
					$user->set_auth_method($auth_module);
					if ($this->authorize_user($user, $auth_module)) {
						$this->user = $user;
						return $user;
					}
				}
			}
		}

		return ($this->user === null) ? new User_NoAuth_Model() : $this->user;

	}

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param $username     string    To log in with
	 * @param $password     string    To check against
	 * @param $auth_method  string    Optional authentication method to use
	 * @return boolean                True if success
	 */
	public function login($username, $password, $auth_method = false) {

		if ($auth_method === false) {
			$parts = explode('$', $username, 2);
			if (count($parts) == 2) {
				$username = $parts[0];
				$auth_method = $parts[1];
				$this->log->log('debug',
					'Trying to log in as: ' . var_export($username, true) .
						 ' with explicitly requested (using $) auth method ' .
						 var_export($auth_method, true));
			} else {
				$auth_method = $this->config['default_auth'];
				$this->log->log('debug',
					'Trying to log in as: ' . var_export($username, true) .
						 ' with default auth method ' .
						 var_export($auth_method, true));
			}
		} else {
			$this->log->log('debug',
				'Trying to log in as: ' . var_export($username, true) .
					 ' with method ' . var_export($auth_method, true));
		}

		/*
		 * APC can cache credentials, so no new login lookup is needed when
		 * logging in several times in a row. This is useful when using
		 * http_api.
		 */
		$apc_tag = false;

		if ($this->config['apc_enabled'] && extension_loaded('apc')) {
			/* Generate tag to store with hash */
			$apc_tag = $this->apc_key($username, $auth_method, $password);
			$userdata = apc_fetch($apc_tag, $success);

			/* Userdata can be false = no accesss, or false = not cached */
			if ($success) {
				if ($userdata === false) {
					$this->user = null;
					$this->session_store($this->config['session_key']);

					$this->log->log('debug',
						'Using cached credentials: authentication failed');
					return false;
				} else {
					/* Don't postprocess user, already authorized */
					$this->user = new User_Model($userdata);
					$this->session_store($this->config['session_key']);

					$this->log->log('debug',
						'Using cached credentials: authentication success');
					return true;
				}
			} else {
				$this->log->log('debug', 'No cached credentials... logging in');
			}
		}

		$driver = $this->get_auth_driver($auth_method);
		if ($driver === false) {
			$this->log->log('debug', "No auth driver found for method '$auth_method' for user '$username'");
			return false;
		}

		$user = $driver->login($username, $password);
		if ($user === null) {
			$this->log->log('debug', "Failed to authenticate '$username' using driver '" . get_class($driver) . "'");
			return false;
		}

		/* Postprocess login */
		$user->set_auth_method($auth_method);
		if ($this->authorize_user($user, $auth_method)) {

			$this->user = $user;
			$this->session_store($this->config['session_key']);

			if ($apc_tag !== false) {
				$this->log->log('debug', 'Storing credentials to cache');
				apc_store($apc_tag, $user->export(), (int) $this->config['apc_ttl']);
			}

			return true;
		}

		$this->log->log('debug', "Failed to authorize in '$username' using driver '" . get_class($driver) . "'");

		if ($apc_tag !== false) {
			$seconds = (int) $this->config['apc_ttl'];
			$this->log->log('notice',
				"User '$username' is not authenticated, storing in APC for $seconds seconds to avoid spamming login backend with bad auth");
			apc_store($apc_tag, false, $seconds);
		}
		return false;
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @return boolean if successful
	 */
	public function logout() {

		if (($this->user instanceof User_Model)) {
			$driver = $this->get_auth_driver($this->user->get_auth_method());
			if ($driver) {
				$driver->logout($this->user);
			}
		}

		$this->user = null;
		$this->session_clear($this->config['session_key']);
		$this->session_destroy();

		return true;

	}

	/**
	 * Returns true if current session has access for a given authorization
	 * point
	 *
	 * @param $authorization_point string
	 * @return boolean true if access
	 */
	public function authorized_for($authorization_point) {
		return $this->get_user()->authorized_for($authorization_point);
	}

	/**
	 * Given a list of groups, return an associative array with groups as
	 * keys and a boolean if group is available in the backend. If it is
	 * unknown if the user is available, the field is unset.
	 *
	 * @param $grouplist array List of groups to check
	 * @return array An array of all auth_methods as keys, values is an
	 * 	associative array of the groups in $grouplist as keys, boolean as
	 * 	values
	 */
	public function groups_available(array $grouplist) {
		if(!$grouplist) {
			return array();
		}
		$result = array ();
		foreach ($this->auth_modules as $module) {
			// All drivers needs to be fetched and checked...
			$modulename = $module->get_modulename();
			$driver = $this->get_auth_driver($modulename);
			try {
				$result[$modulename] = $driver->groups_available($grouplist);
				foreach ($grouplist as $group) {
					$available = in_array($group, array('meta_all_users', 'meta_driver_' . $modulename), true);
					if (!isset($result[$modulename][$group])) {
						$result[$modulename][$group] = $available;
					} else {
						$result[$modulename][$group] |= $available;
					}
				}
			} catch (Exception $e) {
				/* If a module fails, make groups unknown... */
				/* TODO: Throw error further? */
				$result[$modulename] = array();
			}
		}

		return $result;
	}

	/**
	 * Given a username, return a list of it's groups.
	 * Useful when giving permissions to a user.
	 *
	 * @param $username string
	 *        	User to search for
	 * @return array An array with an element per driver containing the user
	 *         (key=name) with a list of groups as values
	 */
	public function groups_for_user($username) {
		$groups = array ();
		foreach ($this->auth_modules as $module) {
			// All drivers needs to be fetched and checked...
			$auth_method = $module->get_modulename();
			$driver = $this->get_auth_driver($auth_method);
			$driver_groups = $driver->groups_for_user($username);
			if ($driver_groups) {
				$groups[$auth_method] = $driver_groups;
				if (count($driver_groups)) {
					$groups[$auth_method][] = 'meta_all_users';
					$groups[$auth_method][] = 'meta_driver_' . $auth_method;
				}
			}
		}
		if (empty($groups)) {
			$this->log->log('warning', "User $username is not a member of any group and is not given any permissions");
		}
		return $groups;
	}

	/**
	 * Returns name of default authentication method.
	 *
	 * @return string default authentication method
	 * @throws Exception
	 *
	 */
	public function get_default_auth() {
		if (!array_key_exists('default_auth', $this->config))
			throw new Exception("Your configuration does not contain a common: default auth method");
		return $this->config['default_auth'];
	}

	/**
	 * Verify password for a logged in user.
	 *
	 * Usable for form validation of critical user data, for example validate a
	 * password change.
	 *
	 * This method doesn't use APC
	 *
	 * @param $user User_Model
	 *        	User object to verify
	 * @param $password string
	 *        	Password to test
	 * @throws Exception if the user's auth_driver is not valid
	 * @return boolean true if user can be logged in with given password
	 */
	public function verify_password(User_Model $user, $password) {
		$driver = $this->get_auth_driver($user->get_auth_method());
		if (!$driver) {
			throw new Exception('User is authenticated with an unknown backend.');
		}
		return (boolean) $driver->login($user->get_username(), $password);
	}

	/**
	 * Generate a key for APC cache to store login information
	 *
	 * @param $username     string  username
	 * @param $auth_method  string  authentication method
	 * @param $password     string password
	 * @return string tag
	 */
	private function apc_key($username, $auth_method, $password) {
		return $this->config['apc_store_prefix'] .
			 md5($username . '$' . $auth_method . ':' . $password);
	}

	/**
	 * Lazy loading of drivers...
	 *
	 * @param  $module string
	 * @return op5AuthDriver
	 */
	public function get_auth_driver($module) {

		$module = $this->auth_modules->reduce_by('modulename', $module, '=')->one();

		if (!$module) return false;
		if (
			isset($this->drivers[$module->get_modulename()]) &&
			$this->drivers[$module->get_modulename()] instanceof op5AuthDriver
		) {
			return $this->drivers[$module->get_modulename()];
		}

		$properties = $module->get_properties();
		$drivername = $properties['driver'];

		$file_name = 'AuthDriver_' . $drivername;
		$class_name = 'op5' . $file_name;

		require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . $file_name . '.php');

		$this->drivers[$module->get_modulename()] = new $class_name($module);
		return $this->drivers[$module->get_modulename()];
	}

	/**
	 * Authorize user by updating it's auth_data field
	 *
	 * @param $user User_Model User to update
	 * @return boolean If authorization was completed successfully
	 */
	protected function authorize_user(User_Model $user) {
		return op5Authorization::factory()->authorize($user);
	}

	/**
	 * Stores user to session
	 *
	 * @return void
	 */
	protected function session_store($key) {
		if ($this->user instanceof User_Model && $key) {
			$_SESSION[$key] = $this->user->export();
		} else {
			$this->session_clear($key);
		}
	}

	/**
	 * Fetches user object from session held under $key
	 *
	 * @param $key string The session user key to fetch
	 * @return User_Model|null
	 */
	protected function session_fetch($key) {
		if (
			isset($_SESSION[$key]) && is_array($_SESSION[$key])
		) {
			return new User_Model($_SESSION[$key]);
		}
		return null;
	}

	/**
	 * Unsets user from session held under the $key
	 *
	 * @param $key string The session user key
	 * @return void
	 */
	protected function session_clear($key) {
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}

	/**
	 * Destroy the session regardless of wheter initialized or not
	 *
	 * @return void
	 */
	public function session_destroy() {
		if(PHP_SAPI == 'cli') return;
		if (session_id() !== '') {
			$name = session_name();
			@session_destroy();

			$_SESSION = array();
			unset($_COOKIE[$name]);
			if(!headers_sent()) {
				setcookie($name, '', -86400, null, null, false, false);
			}
		}
	}

	/**
	 * Forces authentication and authorization of supplied user.
	 * Authorization of user is optional.
	 *
	 * @param $user User_Model
	 * @param $authorize boolean
	 * @return User_Model
	 */
	public function force_user(User_Model $user, $authorize = true) {
		$this->logout();
		$this->user = $user;
		if ($authorize) $this->authorize_user($user);
		$this->session_store($this->config['session_key']);
		return $this->user;
	}

	/**
	 * Write information back to the backend. Changes after this call won't be
	 * saved for future use or next session, but information should still be
	 * accessable.
	 *
	 * Since Auth is quite close to sessions, and sessions acts as a mutex per
	 * session, it's impossible to have the same session open in multiple
	 * instances of php at the same time.
	 *
	 * write_close should close the session, stop the possibility to continue to
	 * change the authentication settings, and making it possible to start a new
	 * php request to the same session simultanously, if the current request will
	 * take time. For example outputting log data.
	 *
	 * This should prefferably be called between the controller execution, which
	 * might mutate the auth information, and the view controller, for which the
	 * authentication should be read only.
	 */
	public function write_close() {
		if($this->config['session_key'] !== false) {
			$this->session_store($this->config['session_key']);
			session_write_close();
		}
		$this->config['session_key'] = false;
	}

	/**
	 * Return the combined metadata for all modules used in the system.
	 *
	 * This is useful to retreive for example which configuration interfaces
	 * to provide.
	 *
	 * @param string $field
	 * @return array List of drivers, or list of drivers per metadata flag
	 */
	public function get_metadata($field = false) {
		$metadata = array();
		foreach ($this->auth_modules as $module) {
			$driver = $this->get_auth_driver($module->get_modulename());
			$driver_metadata = $driver->get_metadata($field);
			if ($field !== false)
				$driver_metadata = array ($field => $driver_metadata);
			foreach ($driver_metadata as $var => $value) {
				if ($value) {
					if (!isset($metadata[$var]))
						$metadata[$var] = array ();
					$metadata[$var][] = $module->get_modulename();
				}
			}
		}
		if ($field !== false) {
			if (isset($metadata[$var]))
				return $metadata[$var];
			return false;
		}
		return $metadata;
	}

	/**
	 * Rename a group in the configuration files.
	 *
	 * @param $old string Group to rename
	 * @param $new string New name of the group
	 */
	public function rename_group($old, $new) {
		$cfg = op5Config::instance();
		$cfg->cascadeEditConfig('auth_groups.*', 'key', $old, $new);
		$cfg->cascadeEditConfig('auth_users.*.groups.*', 'value', $old, $new);
	}

	/**
	 * Rename a module in the configuration files.
	 *
	 * @param $old string Module to rename
	 * @param $new string New name of the module
	 */
	public function rename_module($old, $new) {
		$cfg = op5Config::instance();
		$cfg->cascadeEditConfig('auth.*', 'key', $old, $new);
		$cfg->cascadeEditConfig('auth.common.default_auth', 'value', $old, $new);
		$cfg->cascadeEditConfig('auth_users.*.modules.*', 'value', $old, $new);
	}

	/**
	 * Implement the actor interface, which just passes the current user
	 * information forward
	 *
	 * @see op5MayI_Actor::getActorInfo()
	 */
	public function getActorInfo() {
		return $this->get_user()->getActorInfo();
	}
} // End Auth
