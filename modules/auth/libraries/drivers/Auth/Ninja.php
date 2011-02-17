<?php
class Auth_Ninja_Driver extends Auth_ORM_Driver
{
	public function login($user, $password, $remember)
	{
		if (empty($user) || empty($password))
			return false;

		if (!is_object($user)) {
			$username = $user;
			$user = User_Model::get_user($username);
		} else {
			$username = $user->username;
		}

		if (!is_object($user)) {
			return false;
		}
		if (ninja_auth::valid_password($password, $user->password, $user->password_algo) === true) {
			$this->complete_login($user);
			return true;
		}

		return false;
	}
}
