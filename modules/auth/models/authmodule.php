<?php


/**
 * Authmodule model
 *
 * @todo: documentation
 */
class AuthModule_Model extends BaseAuthModule_Model {
	/**
	 * Validates a User_Model for this specific AuthModule
	 *
	 * @throws ORMException
	 * @param $user User_Model
	 * @return bool Is valid
	 */
	public function validate_user (User_Model $user) {
		$driver = op5Auth::instance()->get_auth_driver($this->get_modulename());
		if ($driver->get_metadata('require_user_password_configuration')) {
			if (strlen($user->get_password()) === 0) {
				throw new ORMException(
					'Authentication module ' . $this->get_modulename() . ' requires a password'
				);
			}
		}
		return true;
	}
}
