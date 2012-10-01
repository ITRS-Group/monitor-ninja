<?php defined('SYSPATH') OR die('No direct access allowed.');?>

<?php
if (isset($data_str)) {
	if (is_array($data_str)) {
		echo '<div class="report-block">';
		for ($i = 0; $i < count($data_str); $i++) { ?>
		<div class="pie-chart">
			<table>
				<tr><th class="headerNone left"><?php echo help::render('piechart').' '._('Status overview') ?> <?php echo ($data_str[$i]['host'] != '') ? ': '.$data_str[$i]['host'] : ''; ?></th></tr>
				<tr class="even"><td><img src="<?php echo url::site() ?>public/piechart/<?php echo $data_str[$i]['img'] ?>" alt="<?php echo _('Uptime');?>" /></td></tr>
			</table>
		</div>
		<?php
		}
	} else if(!empty($data_str)) { ?>
		<img src="<?php echo url::site()?>public/piechart/<?php echo $data_str ?>" alt="<?php echo _('Uptime');?>" id="pie" />
<?php } } ?>
