<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div align="center" style="padding-top:10px;">
	<?php echo html::image('application/views/themes/default/icons/icon.png',''); ?>

	<h1><?php echo _('Livestatus query failed'); ?></h1>

	<p><?php echo sprintf(_('A livestatus query failed. Make sure nagios is running, livestatus is loaded, and that livestatus is configured to create the socket "%s"'), Kohana::config('database.livestatus.path')); ?></p>
</div>

