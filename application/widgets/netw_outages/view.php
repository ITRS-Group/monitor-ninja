<?php defined('SYSPATH') OR die('No direct access allowed.');
if (!$user_has_access) { ?>
<table class="w-table">
	<tr>
		<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-critical.png'), array('alt' => $label)) ?></td>
		<td><?php echo $no_access_msg; ?></td>
	</tr>
</table>
<?php	 } else { ?>
<table class="w-table">
	<?php if ($total_blocking_outages > 0) { ?>
	<tr>
		<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => $label)) ?></td>
		<td class="status-outages"><?php echo html::anchor('outages/index/', html::specialchars($total_blocking_outages.' '.$label)); ?></td>
	</tr>
	<?php } else { ?>
	<tr>
		<td class="dark"><?php echo html::image($this->add_path('icons/16x16/shield-not-critical.png'), array('alt' => $label)) ?></td>
		<td><?php echo html::anchor('outages/index/', html::specialchars($this->translate->_('N/A'))); ?></td>
	</tr>
	<?php } ?>
</table>
<?php
} // end if user_has_access
?>
