<?php defined('SYSPATH') OR die('No direct access allowed.');

class Command_Model extends ORM {
	protected $table_names_plural = false;
	protected $has_and_belongs_to_many = array('service');
	protected $primary_key = 'id';
}
