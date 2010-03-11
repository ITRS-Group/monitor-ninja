<?php defined('SYSPATH') OR die('No direct access allowed.');?>

<?php
if (isset($data_str)) {
	if (is_array($data_str)) {
		for ($i = 0; $i < count($data_str); $i++) { ?>
		<div class="pie-chart">
			<table>
				<tr><th class="headerNone left"><?php echo help::render('piechart').' '.$label_status ?> <?php echo ($data_str[$i]['host'] != '') ? ': '.$data_str[$i]['host'] : ''; ?></th></tr>
				<tr class="even"><td><img src="<?echo url::site() ?>reports/piechart/<?php echo $data_str[$i]['img'] ?>" alt="<?php echo $this->translate->_('Uptime');?>" id="pie" /></td></tr>
			</table>
		</div>
		<?php
		}
	} else if(!empty($data_str)) { ?>
		<img src="<?php echo url::site()?>reports/piechart/<?php echo $data_str ?>" alt="<?php echo $this->translate->_('Uptime');?>" id="pie" />
<?php } } ?>
