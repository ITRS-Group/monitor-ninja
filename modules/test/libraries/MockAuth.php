<?php
/**
 * A mock implementation of op5Auth, which defines a hard coded user
 * "mockeduser", which is authorized to do anything.
 */
class MockAuth extends op5auth
{
	/**
	 * Returns true if current session has access for a given authorization
	 * point, which is always for this implementation.
	 *
	 * @param $authpoint string
	 * @return boolean true if access
	 */
	public function authorized_for($authpoint) {
		return true;
	}

	/**
	 * Returns the currently logged in user, which is a fixture "mockeduser"
	 * for this implementation
	 *
	 * @return  mixed
	 */
	public function get_user() {
		$user = new op5User(array(
			"username" => "mockeduser",
			"realname" => "Mocke D. User",
			"email" => "mockeduser@op5.com",
			"authdata" => array()
		));
		return $user;
	}
}
