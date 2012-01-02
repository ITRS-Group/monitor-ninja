<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base NINJA model.
 * Sets necessary objects like session and database
 * @author op5 AB
 */
class Ninja_Model extends Model
{
	public $session = false; /**< The user's current session */
	public $profiler = false; /**< If debugging, this will contain a FirePHP profiler */

	public function __construct()
	{
		parent::__construct();
		$this->profiler = new Profiler;
		$this->session = Session::instance();
	}
}
