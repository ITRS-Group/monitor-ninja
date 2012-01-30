<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * PDF help class
 *
 * @todo since this works for csv files as well, rename the class to something
 * like persist_file_Core
 */
class persist_pdf_Core
{
	/**
	 * Turns my_report.csv into my_report-2011-03-23.csv and, perhaps,
	 * my_report.pdf into my_report-2011-03-23-dsaf43.pdf
	 *
	 * @param $original_file string
	 * @param $save_here string
	 * @throws Exception with reason of failure
	 * @return string filename of new file
	 */
	public function save($original_file, $save_here) {
		$original_file = (string) $original_file;
		$save_here = (string) $save_here;
		if(!is_readable($original_file)) {
			throw new Exception("Can't read original file '$original_file'");
		}
		if(!is_writable(pathinfo($save_here, PATHINFO_DIRNAME))) {
			throw new Exception("Can't write output file '$save_here'");
		}
		$file_extension = 'pdf';
		if('.csv' == substr($save_here, -4 , 4)) {
			$file_extension = 'csv';
		}

		$local_persistent_filepath = pathinfo($save_here, PATHINFO_DIRNAME);
		$stripped_name = pathinfo($original_file, PATHINFO_BASENAME);
		$local_persistent_filepath .= '/'.preg_replace('/\.'.$file_extension.'$/', null, $stripped_name).'-'.date('Y-m-d').'.'.$file_extension;
		$safety_limit = 1000;
		$current_try = 1;
		while(file_exists($local_persistent_filepath)) {
			$local_persistent_filepath = preg_replace(
				'~(-[a-h0-9]{6})?\.'.$file_extension.'$~',
				'-'.substr(md5(rand()), 0, 6).'.'.$file_extension,
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
		return $local_persistent_filepath;
	}
}
