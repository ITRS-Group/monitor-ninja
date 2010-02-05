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
		<h1><?php echo $str_source; ?></h1>
		<?php
		if (!$create_pdf) { ?>
		<img src="/ninja/index.php/reports/barchart/<?php echo $report['data_str'] ?>" alt="<?php echo $t->_('Uptime');?>" id="pie" class="chart-border" /><?php
		} else {
			echo "#chart_placeholder_$nr#";
		} ?>
		<!--<a href="<?php #echo $report['avail_links'] ?>">
			<img
				src="../chart.php?type=sla_bar&amp;data=<?php echo $report['data_str'] ?>"
				title="Click to view the corresponding availability report"
				alt="SLA data"
				id="sla-graph_<?php echo $nr;?>" />
		</a>-->
	</div>
	<div id="slaChart<?php echo $nr ?>"></div>
	<?php } if (!empty($report['table_data'])) { ?>
	<div class="sla_table">
		<h1><?php echo $str_source; ?></h1>
		<!--<div class="icon-help" onclick="general_help('sla-table')"></div>-->
			<?php foreach ($report['table_data'] as $source => $data) { ?>
			<table class="auto" border="1">
				<tr>
					<th class="headerNone"></th>
					<?php
						$n = 0;
						foreach ($data as $month => $values) {
						$n++;
					?>
					<th class="headerNone"><?php echo $month ?></th>
					<?php } ?>
				</tr>
				<tr class="even">
					<td class="label"><?php echo $t->_('SLA') ?></td><?php
					$j = 0;
					foreach ($data as $month => $value) {
						$j++; ?>
					<td  class="data"><?php echo $this->_format_report_value($value[0][1]) ?> %</td>
					<?php
					} ?>
				</tr>
				<tr class="odd">
					<td><?php echo $t->_('Real') ?></td><?php
					$i = 0;
					foreach ($data as $month => $value) {
						$i++;?>
					<td class="data">
						<?php echo $this->_format_report_value($value[0][0]) ?> %
						<?php
								echo html::image($this->add_path('icons/12x12/shield-'.(($value[0][0] < $value[0][1]) ? 'down' : 'up').'.png'),
								array('alt' => ($value[0][0] < $value[0][1]) ? $t->('Below SLA') : $t->('OK'),
								'title' => (($value[0][0] < $value[0][1]) ? $t->('Below SLA') : $t->('OK')),'style' => 'width: 12px; height: 12px'));
						?>
					</td>
					<?php } ?>
				</tr>
			</table><?php
			} ?>
	</div><?php
	}

	if (isset ($report['member_links']) && count($report['member_links']) > 0 && !$create_pdf) { ?>
	<div class="setup-table members">
		<h1><?php echo $report['group_title'] ?></h1>
		<div class="icon-help" onclick="general_help('custom-group')"></div>
			<ul id="group_<?php echo $nr;?>"><?php
				foreach($report['member_links'] as $member_link) {
					echo "<li>".$member_link."</li>\n";
				}
				?>
			</ul>
		</div>
		<script type='text/javascript'>
			show_hide('group_<?php echo $nr;?>', document.getElementById('members_<?php echo $nr;?>'))
		</script><?php
	}
}?>
