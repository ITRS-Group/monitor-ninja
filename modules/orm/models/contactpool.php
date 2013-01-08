<?php

require_once( dirname(__FILE__).'/base/basecontactpool.php' );

class ContactPool_Model extends BaseContactPool_Model {
	/**
	*	Fetch contact information
	*/
	public static function get_current_contact()
	{
		$username = Auth::instance()->get_user()->username;
		
		$set = ContactPool_Model::all();
		$set = $set->reduce_by('name', $username, '=');
		
		
		$result = iterator_to_array($set,false);
		
		if(count($result) != 1)
			return false;
		
		return $result[0];
	}
}
