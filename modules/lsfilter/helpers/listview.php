<?php

class listview {
	/**
	 * A simple tool to fetch a set of elements reduced by a certain paramters
	 * 
	 * Useful for generating, for example a set of all notifications related to a host.
	 * 
	 * @code
	 * $set = listview::set('notifications', array('host_name'=>$hostname));
	 * @endcode
	 * 
	 * @param $table table to search in
	 * @param $match array of matches
	 * @return ObjectSet_Model
	 */
	public static function set($table, $match) {
		$set = ObjectPool_Model::pool($table)->all();
		/* @var $elems ObjectSet_Model */
		foreach($match as $key => $value) {
			$set = $set->reduce_by($key, $value, '=');
		}
		return $set;
	}

	/**
	 * A simple tool to generate a search query, given a table name and an array of matches
	 *
	 * Useful for generating, for example a query of all notifications related to a host.
	 *
	 * @code
	 * $query = listview::query('notifications', array('host_name'=>$hostname));
	 * @endcode
	 *
	 * @param $table table to search in
	 * @param $match array of matches
	 * @return string such as '[hosts] host_name="host-1"'
	 */
	public static function query($table, $match) {
		return self::set($table, $match)->get_query();
	}

	/**
	 * A simple tool to generate a link to a lsitview, given a table name and an array of matches
	 *
	 * Useful for generating, for example a link to all notifications related to a host.
	 *
	 * @code
	 * $url = listview::link('notifications', array('host_name'=>$hostname));
	 * @endcode
	 *
	 * @param $table table to search in
	 * @param $match array of matches
	 * @return string
	 */
	public static function link($table, $match) {
		return url::base(true) . "listview/?q=" . urlencode(self::query($table, $match));
	}
}
