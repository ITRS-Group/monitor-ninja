<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table style="height: 100%;" summary="Network health" class="health">
	<tr style="height: 100%;">
		<td style="height: 100%;text-align: center; width: 50%">
			<div  style="height: 100%; position: relative;">
				<?php echo html::image($host_image, array('style' => 'height:'.round($host_value).'%; width: 100%; left: 0; bottom: 0; position: absolute;', 'alt' => $host_label)) ?>
				<div style="<?php echo ($host_value > 33) ? 'color: #ffffff;' : ''?>font-size: 140%; position: absolute; left: 8px; bottom: 24px;"><?php echo round($host_value) ?> %</div>
				<div style="<?php echo ($host_value > 12) ? 'color: #ffffff;' : ''?>font-size: 90%; position: absolute; left: 8px; bottom: 8px;"><?php echo $host_label ?></div>
			</div>
		</td>
		<td style="height: 100%; text-align: center; width: 50%">
			
			<div style="height: 100%; position: relative;">
				<?php echo html::image($service_image, array('style' => 'height:'.round($service_value).'%; width: 100%; left: 0; bottom: 0; position: absolute;', 'alt' => $service_label)) ?>
				<div style="<?php echo ($service_value > 33) ? 'color: #ffffff;' : ''?>font-size: 140%; position: absolute; left: 8px; bottom: 24px;"><?php echo round($service_value) ?> %</div>
				<div style="<?php echo ($service_value > 12) ? 'color: #ffffff;' : ''?>font-size: 90%; position: absolute; left: 8px; bottom: 8px;"><?php echo $service_label ?></div>
			</div>
		</td>
	</tr>
</table>
