<?php

require_once( dirname(__FILE__).'/base/basecolumnset.php' );

/**
 * Describes a set of objects from livestatus
 */
class ColumnSet_Model extends BaseColumnSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitor.monitoring.columns";
	}
}
