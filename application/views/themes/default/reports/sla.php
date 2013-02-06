<?php defined('SYSPATH') OR die('No direct access allowed.');

$nr = 0;
foreach($report_data as $i =>  $report) {
	$nr++;
	if (!empty($report['data_str'])) {
		if (!$report['name']) {
			$str_source = 'SLA breakdown for Custom group';
		}
		else {
			if(is_array($report['name']))
				$report['name'] = implode(', ', $report['name']);
			if (!$options['use_alias'] || count($report['source']) > 1)
				$str_source = _('SLA breakdown for').': '.$report['name'];
			else
				$str_source = _('SLA breakdown for').': '.$this->_get_host_alias($report['name']).' ('.$report['name'].')';
		}
	}
	?>
	<div class="setup-table members">
		<h2 style="margin-top: 20px; margin-bottom: 4px"><?php echo help::render('sla_graph').' '.$str_source; ?></h2>
		<form action="<?php echo url::site() ?>avail/generate" method="post">
			<input type="image" class="report-chart-fullwidth" src="<?php echo url::site() ?>public/barchart/<?php echo $report['data_str'] ?>" title="<?php echo _('Uptime');?>" />
			<?php
			echo $options->as_form(true);
			$names = $report['name'];
			if (!is_array($names))
				$names = array($names);
			foreach ($names as $name) {
				echo '<input type="hidden" name="'.$options->get_value('report_type').'[]" value="'.$name.'"/>';
			}
			?>
		</form>
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
				<td class="data"><?php echo reports::format_report_value($value[1]) ?> %</td>
				<?php
				} ?>
			</tr>
			<tr class="odd">
				<td><?php echo _('Real') ?></td><?php
				$y = 0;
				foreach ($data as $month => $value) {
					$y++;?>
				<td class="data">
					<?php echo reports::format_report_value($value[0]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(($value[0] < $value[1]) ? 'down' : 'up').'.png'),
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
	if (count($members) > 1) { ?>
	<div class="members">

		<table style="margin-bottom: 20px;">
			<caption style="margin-top: 15px;"><?php echo help::render('sla_group_members').' '._('Group members');?></caption>
			<tr><th><?php echo is_string($report['name']) ? $report['name'] : _('Custom group') ?></th></tr>
			<?php
				$x = 0;
				if (strpos('service', $options['report_type']) !== false) {
					$type = 'services';
					$objname = 'service_description';
				}
				else {
					$type = 'hosts';
					$objname= 'host_name';
				}
				foreach($members as $member) {
					$x++;
					echo '<tr class="'.($x%2 == 0 ? 'odd' : 'even').'"><td>';
					echo '<a href="'.url::site().'sla/generate?'.$objname.'[]='. $member. '&report_type='.$type.'&amp;'.$options->as_keyval_string(true).'">'.$member.'</a>';
					echo "</td></tr>\n";
				}
				?>
		</table>
	</div>
	<?php } } ?>
