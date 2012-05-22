<?php defined('SYSPATH') OR die("No direct access allowed");
if (isset($schedules)) {
	echo $schedules;
}
?>
<div class="w98 left">
	<h1><?php echo _('Most recent hard alerts'); ?></h1>
	<p style="margin-top:-10px; margin-bottom: 14px"><?php $this->_print_duration($options['start_time'], $options['end_time']); ?></p>
	<table <?php echo ($create_pdf ? 'style="margin-top: 15px" border="1"' : '') ?>>
		<tr>
			<th <?php echo ($create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em; width:20px"' : 'class="headerNone left"') ?>><?php //echo _('State'); ?></th>
			<th <?php echo ($create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left"') ?>><?php echo _('Time'); ?></th>
			<th <?php echo ($create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left"') ?>><?php echo _('Alert Types'); ?></th>
			<th <?php echo ($create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left"') ?>><?php echo _('Host'); ?></th>
			<th <?php echo ($create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left"') ?>><?php echo _('Service'); ?></th>
			<th <?php echo ($create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em;width:70px"' : 'class="headerNone left"') ?>><?php echo _('State Types'); ?></th>
			<th <?php echo ($create_pdf ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left"') ?>><?php echo _('Information'); ?></th>
		</tr>
		<?php
		$i = 0;
		if (count($result)>0 && !empty($result)) {
			foreach ($result as $ary) {
				$i++;
				echo '<tr class="'.($i%2 == 0 ? 'odd' : 'even').'">';
				if (empty($ary['service_description'])) {
					$alert_type = _('Host Alert');
					$ary['service_description'] = 'N/A';
					$state = $host_state_names[$ary['state']];
				} else {
					$alert_type = _('Service Alert');
					$ary['service_description'] = html::anchor(base_url::get().'extinfo/details/?type=service&host='.urlencode($ary['host_name']).'&service='.urlencode($ary['service_description']), $ary['service_description']);
					$state = $service_state_names[$ary['state']];
				}
				$softhard = $ary['hard'] == 1 ? _('Hard') : _('Soft');
		?>
			<td <?php echo ($create_pdf ? 'style="width:20px"' : 'class="icon status"') ?>>
				<?php echo html::image($this->add_path('icons/16x16/shield-'.strtolower($state).'.png'), array('title' => $state, 'alt' => $state)); ?>
			</td>
			<td <?php echo $create_pdf ? 'style="font-size: 0.8em;"' : '' ?>><?php echo date(nagstat::date_format(), $ary['timestamp']); ?></td>
			<td <?php echo $create_pdf ? 'style="font-size: 0.8em;"' : '' ?>><?php echo $alert_type; ?></td>
			<td <?php echo $create_pdf ? 'style="font-size: 0.8em;"' : '' ?>><?php echo html::anchor(base_url::get().'extinfo/details/?type=host&host='.urlencode($ary['host_name']), $ary['host_name']) ?></td>
			<td <?php echo $create_pdf ? 'style="font-size: 0.8em;"' : '' ?>><?php echo $ary['service_description']; ?></td>
			<td <?php echo $create_pdf ? 'style="font-size: 0.8em;width:70px"' : '' ?>><?php echo $softhard; ?></td>
			<td <?php echo $create_pdf ? 'style="font-size: 0.8em;"' : '' ?>><?php echo htmlspecialchars($ary['output']); ?></td>
		</tr>
		<?php }
		} ?>
	</table>
</div>
<?php // printf("Report completed in %.3f seconds<br />\n", $completion_time); ?>
