<?php defined('SYSPATH') OR die('No direct access allowed.');

$nr = 0;
foreach($report_data as $i =>  $report) {
	$nr++;
	if (!$report['name']) {
		$str_source = 'SLA breakdown for custom group';
		$names = array('custom group');
	}
	else {
		$names = $report['name'];
		if (!is_array($names))
			$names = array($names);
		if ($options['use_alias'] && $options['report_type'] !== 'services') {
			foreach ($names as $k => $name)
				$names[$k] = reports::get_alias($options['report_type'], $name).' ('.$name.')';
		}
		$str_source = sprintf(_('SLA breakdown for: %s'), implode(',', $names));
	}
	?>
	<div class="setup-table members">
		<h2 style="margin-top: 20px; margin-bottom: 4px"><?php echo help::render('sla_graph').' '.$str_source; ?></h2>
<?php
	echo form::open(url::site() . 'avail/generate', array(), array('report_type' => $options['report_type']));
	echo form::input(array('type' => 'image',
		'class' => 'report-chart-fullwidth',
		'src' => url::site() . 'public/barchart/?' . $report['data_str'],
		'title' => sprintf(_('Show availability breakdown for %s'), implode(', ', $names))
	));
	echo $options->as_form(true);
	# Stupid multi-group reports, why do you insist on making my life complicated?
	# In case it's a multi-group report we must look at $report['name']
	$obj_names = $report['name'];
	# But if it's empty, this must be a multi-host/multi-service.
	if (!$obj_names)
		$obj_names = $options['objects'];
	# Oh, but it can also be a string, because loose typing is awesome, I'm telling you!
	if (!is_array($obj_names))
		$obj_names = array($obj_names);
	foreach ($obj_names as $name) {
		echo form::hidden('objects[]', $name);
	}
	echo form::close();
?>
	</div>
	<div id="slaChart<?php echo $nr ?>"></div>
	<?php  if (!empty($report['table_data'])) {
		$data = $report['table_data']; ?>
		<div class="sla_table">
		<h2 style="margin: 15px 0px 4px 0px"><?php echo help::render('sla_breakdown').' '.$str_source; ?></h2>
		<table class="auto" border="1">

			<tr>
				<th></th>
				<?php
					$n = 0;
					foreach ($data as $month => $values) {
					$n++;
				?>
				<th><?php echo date('M', $month) ?></th>
				<?php } ?>
			</tr>
			<tr class="even">
				<td class="label"><?php echo _('SLA') ?></td><?php
				$j = 0;
				foreach ($data as $month => $value) {
					$j++; ?>
				<td><?php echo reports::format_report_value($value[1]) ?> %</td>
				<?php
				} ?>
			</tr>
			<tr class="odd">
				<td><?php echo _('Real') ?></td><?php
				$y = 0;
				foreach ($data as $month => $value) {
					$y++;?>
				<td>
					<?php echo reports::format_report_value($value[0]) ?> % <?php echo html::image(ninja::add_path('icons/12x12/shield-'.(($value[0] < $value[1]) ? 'down' : 'up').'.png'),
							array(
							'alt' => '',
							'title' => $value[0] < $value[1] ? _('Below SLA') : _('OK'),
							'style' => 'width: 11px; height: 12px'));
					if (isset($value[2]) && $value[2] > 0) {
						echo "<br />(" . reports::format_report_value($value[2]) ."% in other states)";
					}?></td>
				<?php } ?>
			</tr>
		</table>
	</div>
	<?php }
	$members = $report['source'];
	if ($members && count($members) > 1) { ?>
	<div class="members">

		<table style="margin-bottom: 20px;">
			<caption style="margin-top: 15px;"><?php echo help::render('sla_group_members').' '._('Group members');?></caption>
			<tr><th><?php echo implode(',', $names); ?></th></tr>
			<?php
				$x = 0;
				if (strpos($options['report_type'], 'service') !== false) {
					$type = 'services';
				}
				else {
					$type = 'hosts';
				}
				foreach($members as $member) {
					if ($options['use_alias'] && $type !== 'services')
						$name = reports::get_alias($type, $member).' ('.$member.')';
					else
						$name = $member;
					$x++;
					echo '<tr class="'.($x%2 == 0 ? 'odd' : 'even').'"><td>';
					echo '<a href="'.url::site().'sla/generate?objects[]='. $member. '&report_type='.$type.'&amp;'.$options->as_keyval_string(true).'">'.$name.'</a>';
					echo "</td></tr>\n";
				}
				?>
		</table>
	</div>
	<?php } } ?>
