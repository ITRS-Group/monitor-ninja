<?php

require_once( dirname(__FILE__).'/base/basecommandset.php' );

/**
 * Describes a set of objects from livestatus
 */
class CommandSet_Model extends BaseCommandSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitoring.commands";
	}
}
