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
	 * Log in an already authenticated Keycloak user
	 *
	 * @param
	 *        	string username to log in
	 * @param
	 *        	string password not used
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
		// TODO: Check that user has keycloak as auth module
		return $user;
	}

	private function fetch_users() {
		if ($this->users) return;
		$this->users = UserPool_Model::all();
	}
}
