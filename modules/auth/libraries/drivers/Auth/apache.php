<?php
class Auth_Apache_Driver extends Auth_ORM_Driver
{
	public function login($username, $password, $remember)
	{
		if (!empty($username)) {
			$user = User_Model::get_user($username);
			$this->complete_login($user);
		} else {
			header('location: ' . Kohana::config('auth.apache_login'));
		}
		return true;
	}
}
