<?php
class Auth_Apache_Driver extends Auth_ORM_Driver
{
	public function login($username, $password, $remember)
	{
		if (!empty($username)) {
			Cli_Controller::insert_user_data();
			$user = User_Model::get_user($username);
			Auth::instance()->force_login($user->username);
		} else {
			header('location: ' . Kohana::config('auth.apache_login'));
		}
		return true;
	}
}
