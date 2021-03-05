<?php
use Jumbojett\OpenIDConnectClientException;

class Keycloak_Controller extends Chromeless_Controller {

	/**
	 * Handle callbacks from keycloak
	 */
	public function index () {

		try {

			if (PHP_SAPI !== 'cli' && Kohana::config('cookie.secure') && (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS'])) {
				throw new OpenIDConnectClientException(_('Ninja is configured to only allow logins through the HTTPS protocol. Try to login via HTTPS, or change the config option cookie.secure.'));
			}

			$this->_verify_access('ninja.auth:login');

			$auth = op5auth::instance();
			$modules = $auth->get_modules_by_driver('Keycloak');

			if (empty($modules) || !is_array($modules) || count($modules) == 0) {
				throw new OpenIDConnectClientException(_('No Keycloak module configured.'));
			}

			if (count($modules) > 1) {
				throw new OpenIDConnectClientException(_('Multiple Keycloak modules are not supported -- there can be only one.'));
			}

			// If there exists a uri parameter we want to redirect back there
			// after authenticating so we save it for use later.
			if (array_key_exists('uri', $_GET)) {
				$_SESSION['uri'] = $_GET['uri'];
			}

			$username = false;
			$password= false;
			$auth_method = $modules[0]->get_modulename();

			$result = $auth->login($username, $password, $auth_method);

			if (!$result) {
				throw new OpenIDConnectClientException(_('Login failed - please try again'));
			}

			Event::run('ninja.logged_in');
		} catch(OpenIDConnectClientException $e) {
			return url::redirect(Kohana::config('routes.log_in_form') . '?error=' . urlencode($e->getMessage()));
		}

		/*
		 * If we're logged in, we should go to the requested page. This might be
		 * either because we just have logged in, or already is logged in. Either
		 * way, we shouldn't show a login page.
		 */
		if ($auth->logged_in()) {
			$requested_uri = Kohana::config('routes.logged_in_default');

			if (array_key_exists('uri', $_SESSION)) {
				$requested_uri = $_SESSION['uri'];
				unset($_SESSION['uri']);
			}
			return url::redirect($requested_uri);
		}

		Event::run('ninja.show_login', $this);
	}

}
