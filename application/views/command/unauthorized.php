<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<br />

<p>
	<div class='error_message'>
		<?php echo $error_message ?>
	</div>
</p>
<p>
	<div class='error_description'>
		<?php echo $error_description ?><br /><br />
		<a href='javascript:window.history.back()'><?php echo $return_link_lable ?></a>
	</div>
</p>
