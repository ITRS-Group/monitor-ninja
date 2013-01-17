<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table summary="Network health" class="health" style="table-layout:fixed;">
	<tr>
	<?php foreach( $bars as $bar ):
		if ($bar['value'] < $this->health_critical_percentage) {
			$image = $this->widget_full_path.$this->crit_img;
		} else if ($bar['value'] < $this->health_warning_percentage) {
			$image = $this->widget_full_path.$this->warn_img;
		} else {
			$image = $this->widget_full_path.$this->ok_img;
		}
		?>
		<td style="text-align: center;">
			<div style="<?php echo ($bar['value'] > 33) ? 'color: #ffffff;' : ''?>font-size: 140%; position: absolute; padding-top: 62px; padding-left: 10px;"><?php echo $bar['value'] ?> %</div>
			<div style="<?php echo ($bar['value'] > 12) ? 'color: #ffffff;' : ''?>font-size: 90%; position: absolute; padding-top: 84px; padding-left: 10px;"><?php echo $bar['label'] ?></div>
			<div class="border">
				<?php echo html::image($image, array('style' => 'height:'.$bar['value'].'px; width: 100%; padding-top: '.(100-round($bar['value'])).'px', 'alt' => $bar['label'])) ?>
			</div>
		</td>
	<?php endforeach; ?>
	</tr>
</table>
