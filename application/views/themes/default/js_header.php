<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($js))
	foreach ($js as $js_file) {
		if ($js_file[0] === '/' || strpos($js_file, 'http') === 0) {
			echo '<script type="text/javascript" src="'.$js_file.'"></script>';
		} else {
			echo html::script($js_file);
		}
	}

?>
