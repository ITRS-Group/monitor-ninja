<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget collapsable right editable" id="widget-<?php echo $widget_id ?>" style="margin-right: 1%;width:500px">
	<div class="widget-header dark">
		<span><?php echo $host_title ?></span>
		<span style="margin-left: 145px"><?php echo $service_title ?></span>
	</div>
		<div class="widget-editbox">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<fieldset>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider" style="z-index:1000"></div>
		</fieldset>
		<?php echo form::close() ?>
	</div>

	<div class="widget-content">
<?php } ?>
		<table class="w-table w50">
		<?php $i=0; foreach ($host_header as $row) { ?>
			<?php echo ($i%2 == 0) ? '<tr>' : '' ?>
				<td class="status icon" style="padding: 4px 7px;">
					<?php
					if ($row['lable'] > 0)
						echo html::image($this->add_path('icons/12x12/shield-'.strtolower($row['status']).'.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px'));
					else
						echo html::image($this->add_path('icons/12x12/shield-not-'.strtolower($row['status']).'.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px'));
					?>
				</td>
				<td style="width: 85px"><?php echo html::anchor($row['url'], html::specialchars($row['lable'].' '.$row['status'])) ?></td>
			<?php echo ($i%2 == 1) ? '</tr>' : '' ?>
			<?php	 $i++; } ?>
			<tr>
				<td class="status icon" style="padding: 4px 7px"><?php echo html::image($this->add_path('icons/12x12/shield-info.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px')); ?></td>
				<td><?php echo html::anchor('status/'.$target_method.'/'.$host, html::specialchars($total_hosts.' Hosts')) ?></td>
				<td class="status icon" style="padding: 4px 7px">
					<?php
						if ($total_problems > 0)
							echo html::image($this->add_path('icons/12x12/shield-warning.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px'));
						else
							echo html::image($this->add_path('icons/12x12/shield-not-warning.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px'));
					?>
				</td>
				<td><?php echo html::anchor('status/host/'.$host.'/'.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), html::specialchars($total_problems.' Problems')) ?></td>
			</tr>
		</table>

		<table class="w-table service-totals">
			<?php $i = 0;foreach ($service_header as $row) { ?>
			<?php echo ($i%2 == 0) ? '<tr>' : '' ?>
				<td class="status icon" style="padding: 4px 7px">
					<?php
						if ($row['lable'] > 0)
							echo html::image($this->add_path('icons/12x12/shield-'.strtolower($row['status']).'.png'), array('alt' => $row['status'],'title' => $row['status'],'style' => 'margin-bottom: -2px')) ;
						else
							echo html::image($this->add_path('icons/12x12/shield-not-'.strtolower($row['status']).'.png'), array('alt' => $row['status'],'title' => $row['status'],'style' => 'margin-bottom: -2px')) ;
					?>
				</td>
				<td style="width: 85px"><?php echo html::anchor($row['url'], html::specialchars($row['lable'].' '.$row['status'])) ?></td>
			<?php echo ($i%2 == 1) ? '</tr>' : ''; ?>
			<?php  $i++; } ?>
				<td class="status icon" style="padding: 4px 7px"><?php echo html::image($this->add_path('icons/12x12/shield-info.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px')); ?></td>
				<td><?php echo html::anchor('status/service/'.$host.'/?hoststatustypes='.$host_state, html::specialchars($svc_total_services.' Services')) ?></td>
			</tr>
			<tr>
				<td class="status icon" style="padding: 4px 7px">
					<?php
						if ($svc_total_problems > 0)
							echo html::image($this->add_path('icons/12x12/shield-warning.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px'));
						else
							echo html::image($this->add_path('icons/12x12/shield-not-warning.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px'));
					?>
				</td>
				<td><?php echo html::anchor('status/service/'.$host.'/?hoststatustypes='.$host_state.'&servicestatustypes='.(nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL), html::specialchars($svc_total_problems.' Problems')) ?></td>
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<?php } ?>