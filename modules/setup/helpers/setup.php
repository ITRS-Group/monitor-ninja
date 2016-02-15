<?php
/**
 * Setup helper functions
 */
class setup {

	/**
         * Determines whether the Setup controller is available for usage,
         * i.e. there is no auth module that does not require user
         * configuration and there are no users configured for the ones that
         * do.
         *
         * @throws ORMException
         * @return boolean Is this controller available
         */
	public static function is_available () {
		$auth = op5Auth::instance();
		foreach (AuthModulePool_Model::all() as $module) {
			$driver = $auth->get_auth_driver($module->get_modulename());
			if (
				$driver->get_metadata('require_user_configuration') &&
				$driver->get_user_count() > 0
			) {
				return false;
			}
		}
		return true;
	}

}
