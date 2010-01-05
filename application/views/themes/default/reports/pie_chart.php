<?php defined('SYSPATH') OR die('No direct access allowed.');?>

<?php
if (isset($data_str)) {
	if (is_array($data_str)) {
		for ($i = 0; $i < count($data_str); $i++) { ?>
		<div class="pie-chart">
			<h1 onclick="show_hide('pie<?php echo $i; ?>',this)">
				<?php echo $label_status ?> <?php echo ($data_str[$i]['host'] != '') ? ': '.$data_str[$i]['host'] : ''; ?>
			</h1>
			<div class="icon-help" onclick="general_help('pie_chart')"></div>
			<img src="/ninja/index.php/reports/piechart/<?php echo $data_str[$i]['img'] ?>" alt="Uptime" id="pie" class="chart-border" />

		</div>
		<?php
		}
	} else if(!empty($data_str)) { ?>
	<div class="pie-chart">
		<h1 onclick="show_hide('pie',this)"><?php echo $label_status ?></h1>
		<div class="icon-help" onclick="general_help('pie_chart')"></div>
		<!--<img src="chart.php?type=pie&amp;data=<?php echo $data_str ?>" alt="Uptime" id="pie" class="chart-border" />-->
		<img src="/ninja/index.php/reports/piechart/<?php echo $data_str ?>" alt="Uptime" id="pie" class="chart-border" />
	</div><?php
	}
} ?>