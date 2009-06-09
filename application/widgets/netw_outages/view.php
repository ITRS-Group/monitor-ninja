<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm left w98" id="widget-netw_outages">
	<div class="widget-header">
		<strong><?php echo $title ?></strong>
	</div>
	<div class="widget-content">
	<?php
		if (!$user_has_access) {
			echo $no_access_msg;
	 	} else { ?>
		<table style="border-spacing: 1px; background-color: #e9e9e0; margin-top: -1px">
			<tr>
				<td class="dark"><?php echo html::image('/application/views/themes/default/icons/16x16/shield-critical.png', array('alt' => $label)) ?></td>
				<td><?php echo html::anchor('outages/index/', html::specialchars($total_blocking_outages.' '.$label)); ?></td>
			</tr>
		</table>
		<?php
		} // end if user_has_access
?>
	</div>
</div>

