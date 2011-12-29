<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base NINJA model.
 * Sets necessary objects like session and database
 * @author op5 AB
 */
class Ninja_Model extends ORM
{
	public $db = false; /**< Yet another $db member, that we could skip if we would just stop inheriting from ORM */
	public $session = false; /**< The user's current session */
	public $profiler = false; /**< If debugging, this will contain a FirePHP profiler */

	public function __construct()
	{
		parent::__construct();
		$this->profiler = new Profiler;
		# we will always need database and session
		$this->db = Database::instance;
		$this->session = Session::instance();
	}
}
