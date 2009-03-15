<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($css))
	foreach ($css as $css_file)
		echo html::stylesheet($css_file);

?>