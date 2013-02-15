<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div style="padding:10px">
	<h1 id="hello_header" title="<?php echo $header_titlestring ?>"><?php echo isset($msg_header) ? $msg_header : ''; ?></h1>
	<p><?php echo $msg_description ?></p>

	<p id="hello_data"><br /><?php
		if (isset($data) && !empty($data)) {
			foreach ($data as $d) {
				echo $d."<br />";
			}
		}
	?></p>
</div>
