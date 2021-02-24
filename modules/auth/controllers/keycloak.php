<?php

class Keycloak_Controller extends Chromeless_Controller {

	/**
	 * Authenticate with keycloak.
	 */
	public function callback () {
		return json::ok(array("bar" => 1));
	}

}
