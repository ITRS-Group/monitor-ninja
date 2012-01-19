<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * PDF help class
 */
class persist_pdf_Core
{
	/**
	 * @param string $original_file
	 * @param string $save_here
	 * @throws Exception with reason of failure
	 */
	public function save($original_file, $save_here) {
		$original_file = (string) $original_file;
		$save_here = (string) $save_here;
		if(!is_readable($original_file)) {
			throw new Exception("Can't read original file '$original_file'");
		}
		$local_persistent_filepath = rtrim(preg_replace('/\.pdf$/', null, $save_here), '/').'/';
		if(!is_writable($local_persistent_filepath)) {
			throw new Exception("Can't write output file '$save_here'");
		}
		$stripped_name = defined('K_PATH_CACHE') ? str_replace(K_PATH_CACHE.'/', null, $original_file) : $original_file;
		$local_persistent_filepath .= date('Y-m-d').'-'.$stripped_name;
		$safety_limit = 1000;
		$current_try = 1;
		while(file_exists($local_persistent_filepath)) {
			$local_persistent_filepath = preg_replace(
				'~(-[a-h0-9]{6})?\.pdf$~',
				'-'.substr(md5(rand()), 0, 6).'.pdf',
				$local_persistent_filepath
			);
			if(++$current_try > $safety_limit) {
				throw new Exception("Do not want to overwrite '$local_persistent_filepath', aborting. Please remove the already existing reports.");
			}
		}
		$able_to_copy = copy($original_file, $local_persistent_filepath);
		if(!$able_to_copy) {
			throw new Exception("Failed to copy '$original_file' to '$local_persistent_filepath'");
		}
	}
}
