<?php

/**
 * Exposes ORM structures through CLI if you type this:
 *
 * ninja/index.php orm/structure
 *
 * It will give you a JSON with properties per table, as such:
 * {"hosts": ["name", ...], ...}
 */
class Orm_Controller extends Ninja_Controller {

	/**
	 * Prints a JSON structure with all ORM objects that Ninja is aware of.
	 */
	public function structure() {
		$orm_structure = Module_Manifest_Model::get('orm_structure');
		$this->template = json::ok_view($orm_structure);
	}
}
