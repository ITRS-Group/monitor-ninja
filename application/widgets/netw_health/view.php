<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm w32 left " id="widget-network_health">
	<div class="widget-header"><?php echo $title ?></div>
	<div class="widget-editbox"><!--Edit the widget here--></div>
	<div class="widget-content">
		<table summary="Network healt" class="healt">
				<tr>
					<td style="text-align: center">
						<div style="color: #ffffff; font-size: 22px; position: absolute; padding-top: 62px; padding-left: 10px;"><?php echo $host_value ?> %</div>
						<div style="color: #ffffff; font-size: 10px; position: absolute; padding-top: 84px; padding-left: 10px"><?php echo $host_label ?></div>
						<div class="border">
							<?php echo html::image($host_image, array('style' => 'height:'.$host_value.'px; width: 100%; padding-top: '.round(100-$host_value).'px', 'alt' => $host_label)) ?>
						</div>
					</td>
					<td style="text-align: center">
						<div style="color: #ffffff; font-size: 22px; position: absolute;padding-top: 62px; padding-left: 10px"><?php echo $service_value ?> %</div>
						<div style="color: #ffffff; font-size: 10px; position: absolute; padding-top: 84px; padding-left: 10px;"><?php echo $service_label ?></div>
						<div class="border">
							<?php echo html::image($service_image, array('style' => 'height:'.$service_value.'px; width: 100%; padding-top: '.round(100-$service_value).'px', 'alt' => $service_label)) ?>
						</div>
					</td>
				</tr>
			</table>
		<?php
			if (!empty($arguments)) {
				foreach ($arguments as $arg) {
					echo $arg.'<br />';
				}
			}
		?>
	</div>
</div>
<div style="clear:both"></div>