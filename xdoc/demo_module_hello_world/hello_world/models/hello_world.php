<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Hello world model example
 * This is where you should place your business logic, database queries and such
 */
class Hello_world_Model extends Model
{
	/**
	*	Simple model example
	*/
	public function get_some_data()
	{
		$data = array(
			'Fruit' => 'Apple',
			'Foo' => 'Baar'
			);

		return $data;
	}
}