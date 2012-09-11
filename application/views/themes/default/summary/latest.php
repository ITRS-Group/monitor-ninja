<?php defined('SYSPATH') OR die("No direct access allowed"); ?>
<div class="w98 left">
	<h1><?php echo _('Most recent hard alerts'); ?></h1>
	<p style="margin-top:-10px; margin-bottom: 14px"><?php date::duration($options['start_time'], $options['end_time']); ?></p>
	<table>
		<tr>
			<th class="headerNone left"><?php //echo _('State'); ?></th>
			<th class="headerNone left"><?php echo _('Time'); ?></th>
			<th class="headerNone left"><?php echo _('Alert Types'); ?></th>
			<th class="headerNone left"><?php echo _('Host'); ?></th>
			<th class="headerNone left"><?php echo _('Service'); ?></th>
			<th class="headerNone left"><?php echo _('State Types'); ?></th>
			<th class="headerNone left"><?php echo _('Information'); ?></th>
		</tr>
		<?php
		$i = 0;
		if (!empty($result)) {
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
			<td class="icon status">
				<?php echo html::image($this->add_path('icons/16x16/shield-'.strtolower($state).'.png'), array('title' => $state, 'alt' => $state)); ?>
			</td>
			<td><?php echo date(nagstat::date_format(), $ary['timestamp']); ?></td>
			<td><?php echo $alert_type; ?></td>
			<td><?php echo html::anchor(base_url::get().'extinfo/details/?type=host&host='.urlencode($ary['host_name']), $ary['host_name']) ?></td>
			<td><?php echo $ary['service_description']; ?></td>
			<td><?php echo $softhard; ?></td>
			<td><?php echo htmlspecialchars($ary['output']); ?></td>
		</tr>
		<?php }
		} ?>
	</table>
</div>
<?php // printf("Report completed in %.3f seconds<br />\n", $completion_time); ?>
