<?php
require '/usr/share/php/random_compat/autoload.php';
require '/usr/share/php/phpseclib/autoload.php';
require __DIR__ . './../libraries/OpenIDConnectClient.php';
use Jumbojett\OpenIDConnectClient;

class Keycloak_Controller extends Chromeless_Controller {

	/**
	 * Handle callbacks from keycloak
	 */
	public function index () {

		try {

			if (PHP_SAPI !== 'cli' && Kohana::config('cookie.secure') && (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS'])) {
				throw new NinjaLogin_Exception(_('Ninja is configured to only allow logins through the HTTPS protocol. Try to login via HTTPS, or change the config option cookie.secure.'));
			}

			$this->_verify_access('ninja.auth:login');

			$auth = op5auth::instance();
			$modules = $auth->get_modules_by_driver('Keycloak');

			if (empty($modules) || !is_array($modules) || count($modules) == 0) {
				throw new NinjaLogin_Exception(_('No Keycloak module configured.'));
			}

			if (count($modules) > 1) {
				throw new NinjaLogin_Exception(_('Multiple Keycloak modules are not supported -- there can be only one.'));
			}

			$properties = $modules[0]->get_properties();

			$oidc = new OpenIDConnectClient(
				$properties['provider_url'],
				$properties['client_id'],
				$properties['client_secret']
			);

			// $oidc->setCertPath('keycloak.cert');
			$oidc->setCodeChallengeMethod('S256');
			$oidc->authenticate();

			$username = $oidc->requestUserInfo('preferred_username');
			$password= false;
			$auth_method = $modules[0]->get_modulename();

			$result = $auth->login($username, $password, $auth_method);

			if (!$result) {
				throw new NinjaLogin_Exception(_('Login failed - please try again'));
			}

			Event::run('ninja.logged_in');
		} catch(Exception $e) {
			// TODO: Fix exception handling
			echo('<p>Exception in keycloak authentication:</p><pre>');
			print_r($e);
			echo('</pre>');
			return;
		}

		/*
		 * If we're logged in, we should go to the requested page. This might be
		 * either because we just have logged in, or already is logged in. Either
		 * way, we shouldn't show a login page.
		 */
		if ($auth->logged_in()) {
			$requested_uri = $this->input->get('uri', Kohana::config('routes.logged_in_default'));
			return url::redirect($requested_uri);
		}

		Event::run('ninja.show_login', $this);
	}

}
