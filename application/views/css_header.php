<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($css)) {
	foreach ($css as $css_file) {
		if (stripos($css_file,'.php') == true)
			echo '<link type="text/css" rel="stylesheet" href="'.str_replace('index.php/','',url::site($css_file)).'" />'."\n";
		else if ($css_file[0] === '/' || strpos($css_file, 'http') === 0)
			echo '<link type="text/css" rel="stylesheet" href="'.$css_file.'" />';
		else
			echo html::stylesheet($css_file);
	}
}

?>
