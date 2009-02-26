<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base NINJA model.
 *
 * Sets necessary objects like session and database
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */

class Ninja_Model extends ORM {

	public $db = false;
	public $session = false;
	public $profiler = false;

	public function __construct()
	{
		parent::__construct();
		$this->profiler = new Profiler;
		# we will always need database and session
		$this->db = new Database;
		$this->session = Session::instance();
	}

}