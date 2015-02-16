<?php

require_once( dirname(__FILE__).'/base/basecommentset.php' );

/**
 * Describes a set of objects from livestatus
 */
class CommentSet_Model extends BaseCommentSet_Model {
	/**
	 * Return resource name of this object
	 * @return string
	 */
	public function mayi_resource() {
		return "monitor.monitoring.comments";
	}
}
