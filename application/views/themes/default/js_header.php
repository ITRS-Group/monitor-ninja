<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($js))
	foreach ($js as $js_file) {
		if (!strstr($js_file, 'http')) {
			echo html::script($js_file);
		} else {
			echo '<script type="text/javascript" src="'.$js_file.'"></script>';
		}
	}

?>