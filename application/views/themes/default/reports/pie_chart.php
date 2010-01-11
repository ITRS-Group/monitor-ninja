<?php defined('SYSPATH') OR die('No direct access allowed.');?>

<?php
if (isset($data_str)) {
	if (is_array($data_str)) {
		for ($i = 0; $i < count($data_str); $i++) { ?>
		<div class="pie-chart">
			<table>
				<tr><th class="headerNone" style="text-align: left"><?php //echo help::render('pie_chart') ?> <?php echo $label_status ?> <?php echo ($data_str[$i]['host'] != '') ? ': '.$data_str[$i]['host'] : ''; ?></th></tr>
				<tr class="even"><td><img src="/ninja/index.php/reports/piechart/<?php echo $data_str[$i]['img'] ?>" alt="Uptime" id="pie" /></td>
			</table>
		</div>
		<?php
		}
	} else if(!empty($data_str)) { ?>
	<div class="pie-chart">
		<?php echo help::render('pie_chart') ?><strong><?php echo $label_status ?></strong><br />
		<img src="/ninja/index.php/reports/piechart/<?php echo $data_str ?>" alt="Uptime" id="pie" class="chart-border" />
	</div>
<?php } } ?>