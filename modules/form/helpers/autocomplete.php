<?php

class autocomplete {

	private static $setup = array();

	public static function add_table ($table, $display, $query) {
		self::$setup[$table] = array(
			"display" => $display,
			"query" => $query
		);
	}

	public static function get_settings ($table) {
		if (isset(self::$setup[$table])) {
			return self::$setup[$table];
		} else {
			throw new Exception("No table '$table' exists to autocomplete upon");
		}
	}

}
