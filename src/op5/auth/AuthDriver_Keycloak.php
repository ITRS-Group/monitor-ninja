<?php
require_once (__DIR__ . '/AuthDriver.php');
require_once '/usr/share/php/random_compat/autoload.php';
require_once '/usr/share/php/phpseclib/autoload.php';
require_once '/opt/monitor/op5/ninja/modules/auth/libraries/OpenIDConnectClient.php';
use Jumbojett\OpenIDConnectClient;

/**
 * User authentication and authorization library.
 */
class op5AuthDriver_Keycloak extends op5AuthDriver {

	protected static $metadata = array (
		'require_user_configuration' => false,
		'require_user_password_configuration' => false,
		'login_screen_dropdown' => false
	);

	private $users = null;

	/**
	 * Log in a user with an OpenID Connect flow towards the Keycloak server.
	 *
	 * @param string $username Not used. Included for API compatibility.
	 * @param string $password Not used. Included for API compatibility.
	 * @return User_Model|null
	 */
	public function login($username, $password) {

		$properties = $this->module->get_properties();

		$oidc = new OpenIDConnectClient(
			$properties['provider_url'],
			$properties['client_id'],
			$properties['client_secret']
		);

		$oidc->providerConfigParam([
			'token_endpoint_auth_methods_supported' => []
		]);

		$oidc->authenticate();

		$username = $oidc->requestUserInfo('preferred_username');

		$this->fetch_users();
		$user = $this->users->reduce_by('username', $username, '=')->one();

		if (!$user) {
			// TODO: Define the 'grp' key somewhere.
			// Maybe make it part of the module properties?
			$groups = $oidc->getVerifiedClaims('grp');
			$user = new User_Model(
				array(
					'username' => $username,
					'groups' => $groups ? $groups : array(),
					'realname' => $username,
					'modules' => array($this->module->get_modulename())
				)
			);
			$user->save();
		} else if (!in_array($this->module->get_modulename(), $user->get_modules(), true)) {
			throw new OpenIDConnectClientException(
				_("User '$username' is not configured to login using the module: {$this->module->get_modulename()}")
			);
		}

		return $user;
	}

	/**
	 * Log out a user on the Keycloak server.
	 *
	 * @param User_Model $user User to log out.
	 */
	public function logout($user) {
		$properties = $this->module->get_properties();

		$oidc = new OpenIDConnectClient(
			$properties['provider_url'],
			$properties['client_id'],
			$properties['client_secret']
		);
		$redirect = "https://" . $_SERVER['HTTP_HOST'] . "/monitor/index.php/" . Kohana::config('routes.log_in_form');
		$oidc->signOut(NULL, $redirect);
	}

	/**
	 * Given a list of groups, return an associative array with groups as
	 * keys and a boolean if group is available in the backend. If it is
	 * unknown if the user is available, the field is unset.
	 *
	 * If driver supports multiple backends, the extra auth_method can be set to
	 * the backend. Otherwise, a superset is should given of all backends
	 *
	 * @param array $grouplist List of groups to check
	 * @return array Associative array of the groups in $grouplist as keys,
	 *               boolean as values
	 */
	public function groups_available(array $grouplist) {

		$users = $this->get_keycloak_users();
		$groups = array();

		foreach ($users as $user) {
			foreach ($user->get_groups() as $group) {
				$groups[$group] = $group;
			}
		}

		$result = array();
		foreach ($grouplist as $group) {
			if (substr($group, 0, 5) == 'user_') {
				$name = substr($group, 5);
				$user = $users->reduce_by('username', $name, '=')->one();
				$result[$group] = (boolean) $user;
			} else {
				$result[$group] = isset($groups[$group]);
			}
		}
		return $result;
	}

	/**
	 * Given a username, return a list of it's groups.
	 * Useful when giving permissions to a user.
	 *
	 * @param string $username User to search for
	 * @return array A list of groups
	 */
	public function groups_for_user($username) {
		$users = $this->get_keycloak_users();
		$user = $users->reduce_by('username', $username, '=')->one();
		if (!$user) {
			return array();
		}
		return $user->get_groups();
	}

	/**
	 * Returns the amount of users configured using this driver.
	 *
	 * @return int The usercount
	 */
	public function get_user_count () {
		$users = $this->get_keycloak_users();
		return count($users);
	}

	private function fetch_users() {
		if ($this->users) return;
		$this->users = UserPool_Model::all();
	}

	private function get_keycloak_users() {
		$this->fetch_users();
		$modulename = $this->module->get_modulename();
		return array_values(array_filter($this->users, function ($u) {
			return in_array($modulename, $u->get_modules(), true);
		}));
	}
}
