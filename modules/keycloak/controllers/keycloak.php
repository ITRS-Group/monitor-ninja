<?php

class Keycloak_Controller extends Chromeless_Controller {

	/**
	 * Handle callbacks from keycloak
	 */
	public function index () {
		return json::ok(array("foo" => 1));
	}

}
