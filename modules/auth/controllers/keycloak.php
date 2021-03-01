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

		$oidc = new OpenIDConnectClient('http://10.0.0.72:8080/auth/realms/OP5/',
										'ninja',
										'61d45c59-cb55-4e39-a470-bf76189b2e58');
		// $oidc->setCertPath('keycloak.cert');
		$oidc->setCodeChallengeMethod('S256');
		try {
			$oidc->authenticate();
		}
		catch (Exception $e) {
			echo("<p>Exception in authenticate:</p><pre>");
			print_r($e);
			echo("</pre>");
		}
		// $name = $oidc->requestUserInfo('given_name');
		// $email = $oidc->requestUserInfo('email');
		$username = $oidc->requestUserInfo('preferred_username');

		// echo($name . "\n");
		// echo($email . "\n");
		// echo($all_attributes . "\n");
		//echo("<pre>");
		//print_r($all_attributes);
		//echo("</pre>");
		// echo("<pre>");
		// var_dump($all_attributes);
		// echo("</pre>");

		$this->login($username);
	}

	public function login ($username) {

		$auth = op5auth::instance();

		try {
			if(PHP_SAPI !== 'cli' && Kohana::config('cookie.secure') && (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS'])) {
				throw new NinjaLogin_Exception(_('Ninja is configured to only allow logins through the HTTPS protocol. Try to login via HTTPS, or change the config option cookie.secure.'));
			}

			$this->_verify_access('ninja.auth:login');

			$password	= false;
			$auth_method = 'Keycloak';

			$result = $auth->login($username, $password, $auth_method);
			if (!$result) {
				throw new NinjaLogin_Exception(_("Login failed - please try again"));
			}

			Event::run('ninja.logged_in');
		} catch(NinjaLogin_Exception $e) {
			$this->template->content->message = new ErrorNotice_Model($e->getMessage());
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
