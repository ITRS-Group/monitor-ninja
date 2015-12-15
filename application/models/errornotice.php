<?php

/**
 * A notice of type Error
 */
class ErrorNotice_Model extends Notice_Model {
	public function get_typename() {
		return "error";
	}
}
