<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>">
	<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox" style="background-color: #ffffff; padding: 15px; float: right; margin-top: -1px; border: 1px solid #e9e9e0; right: 0px; width: 230px">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<fieldset>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider" style="z-index:1000"></div>
		</fieldset><br />
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td><?php echo $this->translate->_('Warning Percentage Level') ?>:</td>
				<td><?php echo form::input(
					array(
						'id' => 'health_warning_percentage',
						'name' => 'health_warning_percentage',
						'style' => 'width:20px',
						'title' => sprintf($this->translate->_('Default value: %s%%'), 90)
					), $health_warning_percentage) ?>%</td>
			</tr>
			<tr>
				<td><?php echo $this->translate->_('Critical Percentage Level') ?>:</td>
				<td><?php echo form::input(
					array(
						'id' => 'health_critical_percentage',
						'name' => 'health_critical_percentage',
						'style' => 'width:20px',
						'title' => sprintf($this->translate->_('Default value: %s%%'), 75)
					), $health_critical_percentage) ?>%</td>
			</tr>
		</table>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
	<?php } ?>
		<table summary="Network healt" class="healt">
				<tr>
					<td style="text-align: center">
						<div style="<?php echo ($host_value > 33) ? 'color: #ffffff;' : ''?>font-size: 22px; position: absolute; padding-top: 62px; padding-left: 10px;"><?php echo $host_value ?> %</div>
						<div style="<?php echo ($host_value > 12) ? 'color: #ffffff;' : ''?>font-size: 10px; position: absolute; padding-top: 84px; padding-left: 10px"><?php echo $host_label ?></div>
						<div class="border">
							<?php echo html::image($host_image, array('style' => 'height:'.$host_value.'px; width: 100%; padding-top: '.round(100-$host_value).'px', 'alt' => $host_label)) ?>
						</div>
					</td>
					<td style="text-align: center">
						<div style="<?php echo ($service_value > 33) ? 'color: #ffffff;' : ''?>font-size: 22px; position: absolute;padding-top: 62px; padding-left: 10px"><?php echo $service_value ?> %</div>
						<div style="<?php echo ($service_value > 12) ? 'color: #ffffff;' : ''?>font-size: 10px; position: absolute; padding-top: 84px; padding-left: 10px;"><?php echo $service_label ?></div>
						<div class="border">
							<?php echo html::image($service_image, array('style' => 'height:'.$service_value.'px; width: 100%; padding-top: '.round(100-$service_value).'px', 'alt' => $service_label)) ?>
						</div>
					</td>
				</tr>
			</table>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<div style="clear:both"></div>
<?php } ?>