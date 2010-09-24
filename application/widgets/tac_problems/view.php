<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>">
	<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox" style="width:250px">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<fieldset>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider"></div>
		</fieldset><br />
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td colspan="2"><?php echo $this->translate->_('Color settings') ?></td>
			</tr>
			<tr>
				<td><?php echo $this->translate->_('Network outages') ?></td>
				<td><input type="color" data-text="hidden" data-hex="true" name="col_outages" id="col_outages" value="<?php echo $col_outages ?>" style="height:10px;width:10px;"></td>
				<td style="width:10px"></td>
				<td><?php echo $this->translate->_('Host Down') ?></td>
				<td><input type="color" data-text="hidden" data-hex="true" name="col_hosts_down" id="col_hosts_down" value="<?php echo $col_host_down ?>" style="height:10px;width:10px;"></td>
			</tr>
			<tr>
				<td><?php echo $this->translate->_('Host Unreachable') ?></td>
				<td><input type="color" data-text="hidden" data-hex="true" name="col_services_critical" id="col_services_critical" value="<?php echo $col_service_critical ?>" style="height:10px;width:10px;"></td>
				<td style="width:10px"></td>
				<td><?php echo $this->translate->_('Service Critical') ?></td>
				<td><input type="color" data-text="hidden" data-hex="true" name="col_hosts_unreachable" id="col_hosts_unreachable" value="<?php echo $col_host_unreachable ?>" style="height:10px;width:10px;"></td>
			</tr>
			<tr>
				<td><?php echo $this->translate->_('Service Warning') ?></td>
				<td><input type="color" data-text="hidden" data-hex="true" name="col_services_warning" id="col_services_warning" value="<?php echo $col_service_warning ?>" style="height:10px;width:10px;"></td>
				<td style="width:10px"></td>
				<td><?php echo $this->translate->_('Service Unknown') ?></td>
				<td><input type="color" data-text="hidden" data-hex="true" name="col_services_unknown" id="col_services_unknown" value="<?php echo $col_service_unknown ?>" style="height:10px;width:10px;"></td>
			</tr>
		</table>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
<?php } ?>
		<table class="w-table">
			<?php for ($i = 0; $i < count($problem); $i++) { ?>
				<tr>
					<td class="dark"><?php echo html::image($this->add_path('icons/24x24/shield-'.strtolower($problem[$i]['status']).'.png'), array('alt' => $problem[$i]['status'])) ?></td>
					<td style="white-space: normal;background:<?php echo $problem[$i]['bgcolor']?>" id="<?php echo $problem[$i]['html_id']?>">
						<strong><?php echo strtoupper($problem[$i]['type']).' '.strtoupper($problem[$i]['status']) ?></strong><br />
						<?php
							echo html::anchor($problem[$i]['url'],$problem[$i]['title']);
							if ($problem[$i]['no'] > 0)
								echo ' / '.html::anchor($problem[$i]['onhost'],$problem[$i]['title2']);
						?>
					</td>
				</tr>
			<?php } if (count($problem) == 0) { ?>
				<tr>
					<td class="dark"><?php echo html::image($this->add_path('icons/24x24/shield-not-down.png'), array('alt' => $this->translate->_('N/A'))) ?></td>
					<td><?php echo $this->translate->_('N/A')?></td>
				</tr>
			<?php } ?>
		</table>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<?php } ?>
