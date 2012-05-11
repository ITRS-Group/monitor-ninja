<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table summary="Network health" class="health">
	<tr>
		<td style="text-align: center">
			<div style="<?php echo ($host_value > 33) ? 'color: #ffffff;' : ''?>font-size: 22px; position: absolute; padding-top: 62px; padding-left: 10px;"><?php echo $host_value ?> %</div>
			<div style="<?php echo ($host_value > 12) ? 'color: #ffffff;' : ''?>font-size: 10px; position: absolute; padding-top: 84px; padding-left: 10px"><?php echo $host_label ?></div>
			<div class="border">
				<?php echo html::image($host_image, array('style' => 'height:'.round($host_value).'px; width: 100%; padding-top: '.(100-round($host_value)).'px', 'alt' => $host_label)) ?>
			</div>
		</td>
		<td style="text-align: center">
			<div style="<?php echo ($service_value > 33) ? 'color: #ffffff;' : ''?>font-size: 22px; position: absolute;padding-top: 62px; padding-left: 10px"><?php echo $service_value ?> %</div>
			<div style="<?php echo ($service_value > 12) ? 'color: #ffffff;' : ''?>font-size: 10px; position: absolute; padding-top: 84px; padding-left: 10px;"><?php echo $service_label ?></div>
			<div class="border">
				<?php echo html::image($service_image, array('style' => 'height:'.round($service_value).'px; width: 100%; padding-top: '.(100-round($service_value)).'px', 'alt' => $service_label)) ?>
			</div>
		</td>
	</tr>
</table>
