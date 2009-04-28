<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php if (count($problem) > 0) { ?>
<div class="widget movable collapsable removable closeconfirm w98 left" id="widget-tac_hosts">
	<div class="widget-header"><?php echo $this->translate->_('Unhandled problems') ?></div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table>
			<?php foreach ($problem as $url => $title) { ?>(
				<tr>
					<td class="dark"><img src="images/icons/32x32/shield-<?php echo $title['severity'] ?>.png" alt="" style="height: 24px" /></td>
					<td><strong><?php echo $title['severity'] ?></strong><br /><em><?php echo $title['number'].' '.$this->translate->_('unhandled problems'); ?></em></td>
				</tr>
			<?php } ?>
		</table>
	</div>
</div>
<?php } ?>