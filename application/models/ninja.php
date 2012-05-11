<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base NINJA model.
 * Sets necessary objects like session and database
 * @author op5 AB
 */
class Ninja_Model extends Model
{
	public $session = false; /**< The user's current session */

	public function __construct()
	{
		parent::__construct();
		$this->session = Session::instance();
	}
}
