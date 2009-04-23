<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Reads program status data
 */
class Program_status_Model extends ORM
{
	protected $table_names_plural = false;
	protected $primary_key = 'instance_id';

	/**
	 * Fetch all info from program_status table
	 */
	public function get_all()
	{
		return ORM::factory('program_status')->find_all();
	}
}
