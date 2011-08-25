<?php defined('SYSPATH') or die('No direct script access.');?>

<div style="padding:30px">
	<h1><?php echo isset($widget_name) ? $widget_name : ''; ?></h1>
	<?php echo isset($err_msg) ? $err_msg : '' ?>
	<?php echo isset($msg) ? $msg : '';

		if (isset($erray) && !empty($erray)) {
			echo "<ul>";
			echo implode('<li>', $erray);
			echo "</ul>";
		} ?>

		<div style="padding-top:10px"><?php echo isset($final_msg) ? $final_msg : ''; ?></div>
</div>