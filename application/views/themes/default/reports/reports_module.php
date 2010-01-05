<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<br />
<div id="error" style="margin:10px"><?php echo isset($error_msg) ? $error_msg : '' ?><br /><br />


<?php
if (isset($label_missing_objects))
	echo "<strong>".$label_missing_objects.":</strong><br />";

echo isset($info) ? $info : '';
?>
</div>