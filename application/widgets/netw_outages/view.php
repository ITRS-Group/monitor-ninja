<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm left w98" id="widget-netw_outages">
	<div class="widget-header">
		<strong><?php echo $title ?></strong>
	</div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<!--This is widget content:<br /><br />-->
	<?php if (!$user_has_access) { ?>

		<?php echo $no_access_msg ?>

		<?php } else { ?>

		<?php echo html::anchor('outages/index/', html::specialchars($total_blocking_outages.' '.$label)); ?>

		<?php

			if (!empty($arguments)) {
				foreach ($arguments as $arg) {
					echo $arg."<br />";
				}
			}
		} // end if user_has_access
?>
	</div>
</div>

