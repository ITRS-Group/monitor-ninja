<?php

/**
 * Static store of autocompletion rules per table.
 */
class autocomplete {

	private static $setup = array();

	/**
 	 * @param $table strring
	 * @param $display string
	 * @param $query string
	 */
	public static function add_table ($table, $display, $query) {
		self::$setup[$table] = array(
			"display" => $display,
			"query" => $query
		);
	}

	/**
	 * @param $table string
	 * @return array
	 * @throws Exception
	 */
	public static function get_settings ($table) {
		if (isset(self::$setup[$table])) {
			return self::$setup[$table];
		} else {
			throw new Exception("No table '$table' exists to autocomplete upon");
		}
	}

}
