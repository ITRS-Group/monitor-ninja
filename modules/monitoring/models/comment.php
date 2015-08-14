<?php

require_once( dirname(__FILE__).'/base/basecomment.php' );

/**
 * Describes a single object from livestatus
 */
class Comment_Model extends BaseComment_Model {

	/**
	 * @ninja orm_command name Delete comment
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon delete-comment.delete
	 * @ninja orm_command mayi_method delete.command
	 * @ninja orm_command description
	 *     Delete a comment.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function delete() {
		$cmd = "DEL_HOST_COMMENT";
		if($this->get_is_service()) {
			$cmd = "DEL_SVC_COMMENT";
		}
		return $this->submit_naemon_command($cmd);
	}
}
