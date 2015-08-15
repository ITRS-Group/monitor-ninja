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

	/**
	 * Get a better name for the comment
	 *
	 * @ninja orm depend[] id
	 * @ninja orm depend[] is_service
	 * @ninja orm depend[] host.name
	 * @ninja orm depend[] service.description
	 * @ninja orm depend[] type
	 * @ninja orm depend[] comment
	 */
	public function get_readable_name() {
		if($this->get_is_service()) {
			return sprintf("%d - %s / %s - %s: %s",
					$this->get_id(),
					$this->get_host()->get_name(),
					$this->get_service()->get_description(),
					$this->get_type(),
					mb_strimwidth($this->get_comment(),0, 30, "...")
					);
		} else {
			return sprintf("%d - %s - %s: %s",
					$this->get_id(),
					$this->get_host()->get_name(),
					$this->get_type(),
					mb_strimwidth($this->get_comment(),0, 30, "...")
					);
		}
	}
}
