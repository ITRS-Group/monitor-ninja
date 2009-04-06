<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<p>
	<div class='errorMessage'>
		<?php echo $error_message ?>
	</div>
</p>
<p>
	<div class='errorDescription'>
		<?php echo $error_description ?><br /><br />
		<a href='javascript:window.history.go(-2)'><?php echo $return_link_lable ?></a>
	</div>
</p>
