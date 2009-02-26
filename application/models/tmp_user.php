<?php defined('SYSPATH') OR die('No direct access allowed.');

class __User_Model extends Ninja_Model {
	protected $table_names_plural = false;
	protected $has_one = array("role");
}
