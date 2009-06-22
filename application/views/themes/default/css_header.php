<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($css)) {
	foreach ($css as $css_file) {
		if (stripos($css_file,'.php') == true)
			echo '<link type="text/css" rel="stylesheet" href="'.url::site($css_file).'" />'."\n";
		else
			echo html::stylesheet($css_file);
	}
}

?>