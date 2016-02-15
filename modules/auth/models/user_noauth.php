<?php
/**
 * User NoAuth model
 *
 * @todo: documentation
 */
class User_NoAuth_Model extends User_Model {

	/**
	 * Constructs a new NoAuth user
	 */
	public function __construct () {
		$this->set_username('notauthenticated');
		$this->set_realname('Not Logged in');
	}

	/**
         * Returns if a user is authorized for a certain authorization point
         *
         * @param $auth_point string
         * @return boolean false
         */
        public function authorized_for($auth_point) {
		return false;
	}

	/**
         * Test if user is authorized for viewing a certain object
         *
         * @param $object_definition string
         *              object name, or array of names defining a "path"
         * @param $object_type string
         *              object type (host/service)
         * @param $case_sensitivity boolean
         */
        public function authorized_for_object($object_type, $object_definition,
                $case_sensitivity = true) {
		return false;
	}

        /**
         * Returns true if logged in
         *
         * @return boolean always true (normal users are logged in, notauth
         *         overrides)
         */
        public function logged_in() {
                return false;
        }

        /**
         * List all contact groups I am a member of
         *
         * TODO: Deprecate? (this method is called from Nagvis)
         *
         * @return array array of groups
         */
        public function get_contact_groups() {
                return array();
        }

}
