<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Easily issue a file to be downloaded instead of viewed inline a browser.
 * Makes you not having to write it again when you suddenly want to support
 * Internet Explorer (hearsay, no idea which versions we support with the
 * following code).
 */
class download {

	/**
	 * @param $filename string
	 * @param $content_length int
	 */
	static function headers($filename, $content_length) {
		header('Content-Description: File Transfer');
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".$filename);
		header("Content-Transfer-Encoding:binary");
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: '.$content_length);
	}

}
