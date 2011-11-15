<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * PDF help class
 */
class persist_pdf_Core
{
	/**
	 * @param string $original_file
	 * @param string $save_here
	 * @return boolean
	 */
	public function save($original_file, $save_here) {
		// @todo bug 612
		if(!is_readable($original_file)) {
			return false;
		}
		$local_persistent_filepath = rtrim(preg_replace('/\.pdf$/', null, $save_here), '/').'/';
		if(!is_writable($local_persistent_filepath)) {
			return false;
		}
		$local_persistent_filepath .= date('Y-m-d').'-'.str_replace(K_PATH_CACHE.'/', null, $filename);
		$could_copy = copy($filename, $local_persistent_filepath);
		if(!$could_copy) {
			return false;
		}
		return true;
	}
}
