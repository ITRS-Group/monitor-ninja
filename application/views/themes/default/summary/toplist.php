<?php defined('SYSPATH') OR die("No direct access allowed");
if (isset($schedules)) {
	echo $schedules;
}
?>

<div class="left w98">
	<h1><?php echo _('Top hard alert producers'); ?></h1>
	<p style="margin-top:-10px; margin-bottom: 14px"><?php $this->_print_duration($options['start_time'], $options['end_time']); ?></p>
	<table>
		<tr>
			<th class="headerNone left"><?php echo _('Rank'); ?></th>
			<th class="headerNone left"><?php echo _('Producer Type'); ?></th>
			<th class="headerNone left"><?php echo _('Host'); ?></th>
			<th class="headerNone left"><?php echo _('Service'); ?></th>
			<th class="headerNone left"><?php echo _('Total Alerts'); ?></th>
		</tr>
		<?php
		$i=0;
	if (count($result)>0 && !empty($result)) {
		foreach ($result as $rank => $ary) {
			$i++;
			echo '<tr class="'.($i%2 == 0 ? 'odd' : 'even').'">';
			if (empty($ary['service_description'])) {
				$producer = _('Host');
				$ary['service_description'] = 'N/A';
			} else {
				$producer = _('Service');
				$ary['service_description'] = html::anchor(base_url::get().'extinfo/details/?type=service&host='.urlencode($ary['host_name']).'&service='.urlencode($ary['service_description']), $ary['service_description']);
			}
		?>
			<td class="icon"><?php echo $rank; ?></td>
			<td><?php echo $producer; ?></td>
			<td><?php echo html::anchor(base_url::get().'extinfo/details/?type=host&host='.urlencode($ary['host_name']), $ary['host_name']) ?></td>
			<td><?php echo $ary['service_description']; ?></td>
			<td><?php echo $ary['total_alerts']; ?></td>
		</tr>
		<?php }
	}?>
	</table>
</div>

<?php // printf("Report completed in %.3f seconds<br />\n", $completion_time); ?>
