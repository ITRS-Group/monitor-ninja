<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!empty($js))
	foreach ($js as $js_file)
		echo html::script($js_file);

?>