<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div align="center" style="padding-top:10px;">
	<?php echo html::image('application/views/themes/default/icons/icon.png',''); ?><br /><br />

	<?php echo $this->translate->_('403 Forbidden'); ?><br /><br />

	<?php echo $this->translate->_("You don't have permission to access this resource."); ?><br />
	<?php echo $this->translate->_('Please contact your administrator.'); ?>
</div>