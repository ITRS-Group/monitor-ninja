<?php

require_once( dirname(__FILE__).'/base/basecontact.php' );

/**
 * Describes a single object from livestatus
 */
class Contact_Model extends BaseContact_Model {

	/**
	 * Get a set of all contact groups this contact is member of
	 *
	 * @return ContactGroupSet_Model
	 */
	public function get_groups_set() {
		return ContactGroupPool_Model::all()->reduce_by('members', $this->get_name(), '>=');
	}
}
