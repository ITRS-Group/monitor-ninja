<?php

require_once('op5/config.php');
require_once('op5/auth/User_NoAuth.php');
require_once('op5/auth/User.php');
require_once('op5/auth/Authorization.php');
require_once('op5/log.php');
require_once('op5/livestatus.php');

/**
 * User authentication and authorization library.
 *
 * @package    Auth
 */
class op5auth {
	/**
	 * Defaults is specified here. Parameters is overwritten from config
	 *
	 * @var array
	 */
	private $config = array(
			'enable_auto_login' => false,
			'apc_enabled'       => false,
			'apc_ttl'           => 60,
			'apc_store_prefix'  => 'op5_auth_',
			'session_key'       => false
			);

	/**
	 * A list of auth modules configs until the module is loaded, then the config is replaced with the module.
	 */
	private $auth_modules = array();

	/**
	 * A list, indexed by authentication driver names, containing lists of auth module names.
	 */
	private $drivers = array();

	private $user = false;

	private static $instance = false;

	/**
	 * Create an instance of Auth.
	 *
	 * @param $config array Elements in the array overrieds the values in the common block of auth config
	 * @param $driver_config array
	 * @return object
	 */
	public static function factory($config = false, $driver_config = false)
	{
		self::$instance = new self($config, $driver_config);
		return self::$instance;
	}

	/**
	 * Returns an instance of op5auth
	 *
	 * @param $config array
	 * @param $driver_config array
	 * @return void
	 **/
	public static function instance($config = false, $driver_config = false)
	{
		if (self::$instance == false)
			self::$instance = self::factory($config, $driver_config);
		return self::$instance;
	}

	/**
	 * Creates an op5Auth instance.
	 *
	 * @param $config array	Elements in the array overrides the values in the common block of auth config
	 * @param $driver_config array
	 * @throws Exception Essential configuration is missing
	 * @return void
	 */
	public function __construct($config = false, $driver_config = false)
	{
		$this->log = op5Log::instance('auth');

		/* Retrieve config file */
		$authconfig = op5Config::instance()->getConfig('auth');

		if($authconfig === null) {
			throw new Exception('auth.yml configuration file not found, are the permissions correct?');
		}

		/* Fetch configuration for common, and store */
		if(!isset($authconfig['common'])) {
			throw new Exception('section "common" not found in auth.yml');
		}

		/* Overwrite defaults with config */
		$this->config = array_merge($this->config, $authconfig['common']);

		/* Add local configs to common config */
		if(is_array($config)) {
			$this->config = array_merge($this->config, $config);
		}

		if (is_array($driver_config))
			$authconfig = array_merge($authconfig, $driver_config);

		/*
		 * Fetch list of auth modules, only blocks containing variable "driver" is treated as a auth modules
		 *
		 * the $this->auth_modules array contains configuration of auth modules until loaded, then the configuration
		 * is changed to the actual auth module object, to enable lazy loading.
		 */
		$this->auth_modules = array();
		foreach($authconfig as $name => $moduleconf) {
			if(isset($moduleconf['driver'])) {
				/* Store the module config */
				$this->auth_modules[$name] = $moduleconf;

				/* Add to index by driver name */
				if(!isset($this->drivers[$moduleconf['driver']])) {
					$this->drivers[$moduleconf['driver']] = array();
				}
				$this->drivers[$moduleconf['driver']][] = $name;
			}
		}

		/* No auth modules means error... */
		if(count($this->auth_modules) == 0) {
			throw new Exception('No authentication driver specified');
		}

		/* If only one auth module, thats always the default */
		if(count($this->auth_modules) == 1) {
			list($drv_name) = array_keys($this->auth_modules);
			$this->config['default_auth'] = $drv_name;
		}
	}

	/**
	 * Get a list of authenticaiton modules for a given driver. False if driver is not found.
	 *
	 * @param   $drivername string Name of the driver
	 * @return              array  List of authenticaiton module names, or false if no moudles found.
	 */
	public function getAuthModulesPerDriver($drivername)
	{
		if(!isset($this->drivers[$drivername])) {
			return false;
		}
		return $this->drivers[$drivername];
	}

	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @return  boolean
	 */
	public function logged_in()
	{
		return $this->get_user()->logged_in();
	}

	/**
	 * Returns the users group memberships
	 *
	 * @return array
	 */
	public function get_groups()
	{
		if(!$this->logged_in()) {
			return array();
		}
		return $this->get_user()->groups;
	}

	/**
	 * Returns the currently logged in user, or NoAuth user.
	 *
	 * @return  mixed
	 */
	public function get_user()
	{
		if($this->user === false) {
			$this->session_fetch();
		}

		/*
		 * This is needed for Apache auth, which doesn't go through login method
		 * But let every auth driver try to auto_login, just to make generic.
		 * FIXME: Don't iterate over all... how and still be generic?
		 */
		if($this->config['enable_auto_login'] && $this->user === false) {
			foreach(array_keys($this->auth_modules) as $auth_method) {
				$driver = $this->getAuthModule($auth_method);
				$user = $driver->auto_login();
				if($user !== false) {
					/* Postprocess login */
					$user->auth_method = $auth_method;
					$this->authorize_user($user, $auth_method);
					$this->user = $user;
					return $user;
				}
			}
		}

		if($this->user === false) {
			return new op5User_NoAuth();
		}
		return $this->user;
	}

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 * Implicitly logout previsouly logged in user, to clear session.
	 *
	 * @param   $username string	Username to log in with
	 * @param   $password string	Password to check against
	 * @param   $auth_method string optional, authentication method to use
	 * @return  boolean  True if success
	 */
	public function login($username, $password, $auth_method = false)
	{
		$this->logout();

		if($auth_method === false) {
			$parts = explode('$', $username, 2);
			if(count($parts) == 2) {
				$username = $parts[0];
				$auth_method = $parts[1];
			}
			else {
				$auth_method = $this->config['default_auth'];
			}
		}

		$this->log->log('debug', 'Trying to log in as: '.var_export($username, true).' with method '.var_export($auth_method, true));


		/*
		 * APC can cache credentials, so no new login lookup is needed when
		 * logging in several times in a row.
		 *
		 * This is useful when using http_api.
		 */
		$apc_tag = false;

		if($this->config['apc_enabled'] && extension_loaded('apc')) {
			/* Generate tag to store with hash */
			$apc_tag = $this->apc_key($username, $auth_method, $password);
			$userdata  = apc_fetch($apc_tag, $success);

			/* Userdata can be false = no accesss, or false = not cached */
			if($success) {
				if($userdata === false) {
					/* No access */
					$this->user = false;

					/* Store to session */
					$this->session_store();

					$this->log->log('debug', 'Using cached credentials: authentication failed');
					return false;
				}
				else {
					/* Don't postprocess user, already authorized */
					$this->user = new op5User($userdata);
					/* Store to session */
					$this->session_store();

					$this->log->log('debug', 'Using cached credentials: authentication success');
					return true;
				}
			}
			else {
				$this->log->log('debug', 'No cached credentials... logging in');
			}
		}

		$driver = $this->getAuthModule($auth_method);
		if($driver === false) {
			return false;
		}
		$user = $driver->login($username, $password);
		if($user !== false) {
			/* Postprocess login */
			$user->auth_method = $auth_method;
			if($this->authorize_user($user, $auth_method)) {
				$this->user = $user;

				/* Store to session */
				$this->session_store();

				/* Store to APC */
				if($apc_tag !== false) {
					$this->log->log('debug', 'Storing credentials to cache');
					apc_store($apc_tag, $user->fields, (int) $this->config['apc_ttl']);
				}
				return true;
			}
		}

		if($apc_tag !== false) {
			$seconds = (int) $this->config['apc_ttl'];
			$this->log->log('notice', "User '$username' is not authenticated, storing in APC for $seconds seconds to avoid spamming login backend with bad auth");
			apc_store($apc_tag, false, $seconds);
		}
		return false;
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @return  boolean  if successful
	 */
	public function logout()
	{
		if(($this->user instanceof op5User) &&
			isset($this->user->auth_driver) &&
			$this->getAuthModule($this->user->auth_driver) !== false)
		{
			/* Second call to getAuthModule is always cheap, due to laziness */
			$driver = $this->getAuthModule($this->user->auth_driver);

			$driver->logout($user);
		}
		$this->user = false;
		$this->session_clear();
		return true;
	}

	/**
	 * Returns true if current session has access for a given authorization point
	 *
	 * @param   $authorization_point string
	 * @return  boolean  true if access
	 */
	public function authorized_for($authorization_point)
	{
		return $this->get_user()->authorized_for($authorization_point);
	}

	/**
	 * Given a list of groups, return an associative array with groups as keys and a boolean
	 * if group is available in the backend. If it is unknown if the user is available, the field
	 * is unset.
	 *
	 * If driver supports multiple backends, the extra auth_method can be set to the backend.
	 * Otherwise, a superset is should given of all backends
	 *
	 * @param $grouplist array   List of groups to check
	 * @return array       An array of all auth_methods as keys, values is an associative array
	 *                     of the groups in $grouplist as keys, boolean as values
	 */
	public function groups_available(array $grouplist)
	{
		$result = array();

		foreach(array_keys($this->auth_modules) as $auth_method) {
			/* All drivers needs to be fetched and checked... cant be optimized, unfourtunatly */
			$driver = $this->getAuthModule($auth_method);

			try {
				$result[$auth_method] = $driver->groups_available($grouplist);

				foreach($grouplist as $group) {
					$avalible = false;
					if($group == 'meta_all_users') {
						$avalible = true;
					} else if($group == 'meta_driver_'.$auth_method) {
						$avalible = true;
					}
					if(!isset($result[$auth_method][$group])) {
						$result[$auth_method][$group] = $avalible;
					} else {
						$result[$auth_method][$group] |= $avalible;
					}
				}

			}
			catch(Exception $e) {
				/* If a module fails, make groups unknown... */
				/* TODO: Throw error further? */
				$result[$auth_method] = array();
			}
		}

		return $result;
	}

	/**
	 * Given a username, return a list of it's groups. Useful when giving permissions to a user.
	 *
	 * @param $username   string  User to search for
	 * @return            array   An array with an element per driver containing the user (key=name) with a list of groups as values
	 */
	public function groups_for_user($username)
	{
		$groups = array();
		foreach(array_keys($this->auth_modules) as $auth_method) {
			/* All drivers needs to be fetched and checked... cant be optimized, unfourtunatly */
			$driver = $this->getAuthModule($auth_method);

			$driver_groups = $driver->groups_for_user($username);
			if($driver_groups !== false) {
				$groups[$auth_method] = $driver_groups;
				if(count($driver_groups)) {
					$groups[$auth_method][] = 'meta_all_users';
					$groups[$auth_method][] = 'meta_driver_'.$auth_method;
				}
			}
		}
		return $groups;
	}

	/**
	 * Returns an array of authentication methods.
	 *
	 * @return  array  list of authentication methods, or false if only a single
	 *                 is available
	 */
	public function get_authentication_methods()
	{
		if(count($this->auth_modules) <= 1) {
			return false;
		}
		return array_keys($this->auth_modules);
	}
	/**
	 * Returns name of default authentication method.
	 *
	 * @return 	string 	default authentication method
	 *
	 */
	public function get_default_auth()
	{
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
	 * @param $user     op5User User object to verify
	 * @param $password string  Password to test
	 * @return          boolean true if password is ok
	 */
	public function verify_password(op5User $user, $password) {
		if(!isset($user->auth_method)) {
			throw new Exception('User is not a user object.');
		}
		$driver = $this->getAuthModule($user->auth_method);
		if($driver === false) {
			throw new Exception('User is authenticated with an unknown backend.');
		}
		return $driver->login($user->username, $password) !== false;
	}

	/**
	 * Update password for a given user.
	 *
	 * @param $user     op5User User object to verify
	 * @param $password string  New password
	 * @return          boolean true if password is ok
	 */
	public function update_password(op5User $user, $password) {
		if(!isset($user->auth_method)) {
			throw new Exception('User is not a user object.');
		}

		/* Clear cache, just to be sure... */
		/* FIXME: $password is wrong... it should be old password...
		if(isset($this->config['apc_enabled']) && $this->config['apc_enabled']) {
			$apc_tag = $this->apc_key($user->username, $user->auth_method, $password);
			apc_delete($apc_tag);
		}
		*/

		$driver = $this->getAuthModule($user->auth_method);
		if($driver === false) {
			throw new Exception('User is authenticated with an unknown backend.');
		}
		return $driver->update_password($user, $password);
	}

	/**
	 * Generate a key for APC cache to store login information
	 *
	 * @param $username     string username
	 * @param $auth_method  string authentication method
	 * @param $password     string password
	 * @return              string tag
	 */
	private function apc_key($username, $auth_method, $password)
	{
		return $this->config['apc_store_prefix'] . md5($username . '$' . $auth_method . ':' . $password);
	}

	/**
	 * Lazy loading of drivers...
	 *
	 * @param $auth_method string
	 * @return auth_method
	 */
	private function getAuthModule($auth_method)
	{
		if(!isset($this->auth_modules[$auth_method])) {
			return false;
		}
		if($this->auth_modules[$auth_method] instanceof op5AuthDriver) {
			return $this->auth_modules[$auth_method];
		}
		//error_log('op5Auth: Loading: ' . $auth_method);
		$drv_name = $this->auth_modules[$auth_method]['driver'];
		$file_name = 'AuthDriver_' . $drv_name;
		$class_name = 'op5' . $file_name;
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . $file_name . '.php'); /* In same directory as this file */

		/* To make it possible to get it's name within the driver */
		$this->auth_modules[$auth_method]['name'] = $auth_method;

		$this->auth_modules[$auth_method] = new $class_name($this->auth_modules[$auth_method]);
		return $this->auth_modules[$auth_method];
	}

	/**
	 * Authorize user by updating it's auth_data field
	 *
	 * @param $user op5User User to update
	 * @return boolean if authorization was done successfully
	 */
	protected function authorize_user(op5User $user)
	{
		/* Authorize user */
		if(!isset($user->groups)) {
			$user->groups = array();
		}
		$authorization = op5Authorization::factory();
		if(!$authorization->authorize($user)) {
			return false;
		}

		return true;
	}

	/**
	 * Stores user to session
	 *
	 * @return void
	 **/
	protected function session_store()
	{
		if($this->config['session_key'] !== false &&
		    ($this->user instanceof op5User) &&
		    is_array($this->user->fields))
		{
			$_SESSION[$this->config['session_key']] = $this->user->fields;
		} else {
			$this->session_clear();
		}
	}

	/**
	 * Fetches user object from session
	 *
	 * @return void
	 **/
	protected function session_fetch()
	{
		if($this->config['session_key'] !== false &&
			isset($_SESSION[$this->config['session_key']]) &&
			is_array($_SESSION[$this->config['session_key']]))
		{
			$this->user = new op5User($_SESSION[$this->config['session_key']]);
		} else {
			$this->user = false;
		}
	}

	/**
	 * Unsets user from session
	 *
	 * @return void
	 **/
	protected function session_clear()
	{
		if($this->config['session_key'] !== false && isset ($_SESSION[$this->config['session_key']])) {
			unset($_SESSION[$this->config['session_key']]);
		}
	}

	/**
	 * Forces authentication and authorization of supplied user.
	 * Authorization of user is optional.
	 *
	 * @param $user object
	 * @param $do_authorization boolean
	 * @return user
	 **/
	public function force_user(op5User $user, $do_authorization = true) {
		$this->logout();
		$this->user = $user;
		if($do_authorization)
			$this->authorize_user($user);
		$this->session_store();
		return $this->user;
	}
} // End Auth
