<?php
if (!isset($object_data) || empty($object_data)) {
	die('no data');
}
$create_pdf = !isset($create_pdf) ? false : $create_pdf;
?>
<br />
<div id="trend_event_display"></div>
<?php if (!isset($is_avail)) { ?>
<h1 style="margin-top: 0px"><?php echo $title ?></h1>
<p style="margin-top: -13px;">
	<?php echo $label_report_period ?>: <?php echo $rpttimeperiod	?>
	<?php //echo $label_duration.': '.$duration ?>
	(<?php echo $str_start_date.' '.$this->translate->_('to').' '.$str_end_date ?>)
</p>

<?php
}

$cell_height = isset($avail_height) ? $avail_height: 50;
$title_str = $this->translate->_('Start: %s, End: %s, Duration: %s, Output: %s');
foreach ($object_data as $obj => $data) {
	$cnt = 0; ?>
	<table style="width:100%;padding:0; border-spacing: 0px; border-collapse: collapse; margin-top: 14px">
		<tr>
			<?php
			if (is_array($data) && !empty($data))
				foreach ($data as $event) {
				$width = 0;
				#$sub_type = isset($event['service_description']) && !empty($event['service_description']) ? 'service' : 'host';
				if (isset($event['duration']) && $event['duration']>0) {
					$width = number_format(($event['duration']/$length)*100, 2);
					$width = ($width < 1 && $width != 0.00) ? '1px' : $width.'%';
				} else {
					continue;
				}
				if ($width == '0.00')
					continue;?>
			<td class="trend_event trend_<?php echo Trends_Controller::_translate_state_to_string($event['state'], $sub_type) ?>"
				<?php if ($create_pdf === false) { ?>title="<?php echo
					sprintf(
						$title_str,
							date('Y-m-d H:i', $event['the_time']),
							date('Y-m-d H:i', ($event['the_time'] + $event['duration'])),
							time::to_string($event['duration']),
							$event['output'] ); ?>"
					<?php }
					if ($create_pdf !== false) { ?>
				style="height:<?php echo $cell_height ?>px;width:<?php echo $width ?>;background-color:<?php echo Trends_Controller::_state_colors($sub_type, $event['state']) ?>"></td>
					<?php } else { ?>
				style="height:<?php echo $cell_height ?>px;width:<?php echo $width ?>;background:url(<?php echo url::base(false).$this->add_path('trends/images/'.Trends_Controller::_translate_state_to_string($event['state'], $sub_type).'.png') ?>)"></td>
					<?php } ?>
		<?php	$cnt++;
			} ?>
		</tr>
		<tr>
			<td style="padding:0" colspan="<?php echo $cnt ?>">
				<table class="time_table">
					<tr>
				<?php	foreach ($resolution_names as $tm) {	?>
						<td class="trend_time" title="<?php echo $tm ?>" <?php echo ($create_pdf !== false) ? 'style="font-size: 0.8em"' : ''; ?>><?php echo $tm ?></td>
				<?php 	} ?>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?php
}
?>
<div style="clear:both"></div>
<?php echo (isset($avail_template) && !empty($avail_template)) ? $avail_template : ''; ?>
