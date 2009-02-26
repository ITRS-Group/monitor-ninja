<?php defined('SYSPATH') OR die('No direct access allowed.');

class Current_status_Model extends Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function test()
	{
		echo "This is ".__FUNCTION__."() method in ".__CLASS__." class = Model is loaded<br />";
	}
}