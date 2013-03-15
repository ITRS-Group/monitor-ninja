<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div align="center" style="padding-top:10px;">
	<?php echo html::image('application/views/icons/icon.png',''); ?><br /><br />

	<?php echo _('404 Not Found'); ?><br /><br />

	<?php echo _('Ooops, the page you requested - '.Router::$current_uri.' - could not be found.'); ?><br />
	<?php echo _('Please contact your administrator.'); ?>
</div>
