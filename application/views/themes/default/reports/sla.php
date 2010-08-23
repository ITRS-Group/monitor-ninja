<?php defined('SYSPATH') OR die('No direct access allowed.');?>
<?php $t = $this->translate; ?>
<?php
$nr = 0;
foreach($report_data as $i =>  $report) {
	$nr++;
	$custom_group = explode(',',$report['source']);
	if (!empty($report['data_str'])) {
		if (count($custom_group) > 1)
			$str_source = 'SLA breakdown for Custom group';
		else {
			if (!$use_alias || $report['group_title'] !== false)
				$str_source = $t->_('SLA breakdown for').': '.$report['source'];
			else
				$str_source = $t->_('SLA breakdown for').': '.$this->_get_host_alias($report['source']).' ('.$report['source'].')';
		}

	?>
	<div class="setup-table members">
		<h2 style="margin-top: 20px; margin-bottom: 4px"><?php echo ((!$create_pdf) ? help::render('sla_graph') : '').' '.$str_source; ?></h2>
		<?php
		if (!$create_pdf) { ?>
		<a href="<?php echo $report['avail_links'];?>" style="border: 0px"><img src="<?php echo url::site() ?>reports/barchart/<?php echo $report['data_str'] ?>" alt="<?php echo $t->_('Uptime');?>" id="pie" class="chart-border" /></a><?php
		} else {
			echo "#chart_placeholder_$nr#";
		} ?>
	</div>
	<div id="slaChart<?php echo $nr ?>"></div>
	<?php  if (!empty($report['table_data'])) { ?>
	<div class="sla_table">
			<?php $h = 0; foreach ($report['table_data'] as $source => $data) { if ($h == 0) { $h++;?>
			<h2 style="margin: 15px 0px 4px 0px"><?php echo ((!$create_pdf) ? help::render('sla_breakdown') : '').' '.$str_source; ?></h2>
			<table class="auto" border="1">

				<tr>
					<th <?php echo ($create_pdf) ? 'style="text-align: right; background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"';?>></th>
					<?php
						$n = 0;
						foreach ($data as $month => $values) {
						$n++;
					?>
					<th <?php echo ($create_pdf) ? 'style="text-align: right; background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone"';?>><?php echo $month ?></th>
					<?php } ?>
				</tr>
				<tr class="even">
					<td <?php echo ($create_pdf) ? 'style="background-color: #fafafa; font-size: 0.9em"' : 'class="label"';?>><?php echo $t->_('SLA') ?></td><?php
					$j = 0;
					foreach ($data as $month => $value) {
						$j++; ?>
					<td <?php echo ($create_pdf) ? 'style="text-align: right; background-color: #fafafa; font-size: 0.9em"' : 'class="data"';?>><?php echo reports::format_report_value($value[0][1]) ?> %</td>
					<?php
					} ?>
				</tr>
				<tr class="odd">
					<td <?php echo ($create_pdf) ? 'style="background-color: #e2e2e2; font-size: 0.9em"' : '';?>><?php echo $t->_('Real') ?></td><?php
					$y = 0;
					foreach ($data as $month => $value) {
						$y++;?>
					<td <?php echo ($create_pdf) ? 'style="text-align: right; background-color: #e2e2e2; font-size: 0.9em"' : 'class="data"';?>>
						<?php echo reports::format_report_value($value[0][0]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(($value[0][0] < $value[0][1]) ? 'down' : 'up').'.png'),
								array(
								//'alt' => (($value[0][0] < $value[0][1]) ? $t->('Below SLA') : $t->('OK')),
								//'title' => (($value[0][0] < $value[0][1]) ? $t->('Below SLA') : $t->('OK')),
								'style' => 'width: 11px; height: 12px'));
						?></td>
					<?php } ?>
				</tr>
			</table>
			<?php }
			 } ?>
	</div>
	<?php } if (isset ($report['member_links']) && count($report['member_links']) > 0 && !$create_pdf) { ?>
	<div class="setup-table members">

		<table style="margin-bottom: 20px;">
			<caption style="margin-top: 15px;"><?php echo ((!$create_pdf) ? help::render('sla_group_members') : '').' '.$this->translate->_('Group members');?></caption>
			<tr><th class="headerNone"><?php echo !empty($report['group_title']) ? $report['group_title'] : $this->translate->_('Custom group') ?></th></tr>
			<?php
				$x = 0;
				foreach($report['member_links'] as $member_link) {
					$x++;
					echo "<tr class=\"".($x%2 == 0 ? 'odd' : 'even')."\"><td style=\" border-right: 1px solid #dcdcdc\">".$member_link."</td></tr>\n";
				}
				?>
			</table>
			<br />
		</div>
	<?php } } } ?>