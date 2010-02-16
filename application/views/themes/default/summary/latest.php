<?php defined('SYSPATH') OR die("No direct access allowed"); ?>

<div class="widget w98 left">
	<h2><?php echo $this->translate->_('Most recent hard alerts'); ?></h2>
	<table>
		<tr>
			<th class="headerNone left"><?php //echo $label_state; ?></th>
			<th class="headerNone left"><?php echo $label_host; ?></th>
			<th class="headerNone left"><?php echo $label_service; ?></th>
			<th class="headerNone left"><?php echo $label_time; ?></th>
			<th class="headerNone left"><?php echo $label_alert_type; ?></th>
			<th class="headerNone left"><?php echo $label_state_type; ?></th>
			<th class="headerNone left"><?php echo $label_information; ?></th>
		</tr>
		<?php
			$i = 0;
			foreach ($result as $ary) {
				$i++;
				echo '<tr class="'.($i%2 == 0 ? 'odd' : 'even').'">';
				if (empty($ary['service_description'])) {
					$alert_type = $label_host_alert;
					$ary['service_description'] = 'N/A';
					$state = $host_state_names[$ary['state']];
				} else {
					$alert_type = $label_service_alert;
					$state = $service_state_names[$ary['state']];
				}
				$softhard = $ary['hard'] == 1 ? $label_hard : $label_soft;
		?>
			<td class="icon status">
				<?php echo html::image($this->add_path('icons/16x16/shield-'.strtolower($state).'.png'), array('title' => $state, 'alt' => $state)); ?>
			</td>
			<td><?php echo $ary['host_name']; ?></td>
			<td><?php echo $ary['service_description']; ?></td>
			<td><?php echo date("Y-m-d H:i:s", $ary['timestamp']); ?></td>
			<td><?php echo $alert_type; ?></td>
			<td><?php echo $softhard; ?></td>
			<td><?php echo $ary['output']; ?></td>
		</tr>
		<?php } ?>
	</table>
</div>
<?php // printf("Report completed in %.3f seconds<br />\n", $completion_time); ?>