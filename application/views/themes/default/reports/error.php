<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<br />
<div id="report_error" style="margin:10px"><?php echo isset($error_msg) ? $error_msg : '' ?></div>

<?php
if (isset($missing_objects)) {
	echo "<strong>"._('Missing objects').":</strong>";
	echo "<ul>".implode('<li>', $missing_objects)."</ul>";
}
