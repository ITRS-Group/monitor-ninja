<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * PDF help class
 */
class persist_pdf_Core
{
	/**
	 * @param $original_file string
	 * @param $save_here string
	 * @throws Exception with reason of failure
	 */
	public function save($original_file, $save_here) {
		$original_file = (string) $original_file;
		$save_here = (string) $save_here;
		// @todo bug 612
		if(!is_readable($original_file)) {
			throw new Exception("Can't read original file '$original_file'");
		}
		$local_persistent_filepath = rtrim(preg_replace('/\.pdf$/', null, $save_here), '/').'/';
		if(!is_writable($local_persistent_filepath)) {
			throw new Exception("Can't write output file '$save_here'");
		}
		$local_persistent_filepath .= date('Y-m-d').'-'.str_replace(K_PATH_CACHE.'/', null, $original_file);
		$able_to_copy = copy($original_file, $local_persistent_filepath);
		if(!$able_to_copy) {
			throw new Exception("Failed to copy '$original_file' to '$local_persistent_filepath'");
		}
	}
}
