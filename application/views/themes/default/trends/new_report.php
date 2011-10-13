<?php

$reporting_period = null;
if(isset($report_time_formatted) && $report_time_formatted) {
	$reporting_period = $reporting_period;
} elseif(isset($rpttimeperiod) && $rpttimeperiod) {
	$reporting_period = $reporting_period;
}

$create_pdf = !isset($create_pdf) ? false : $create_pdf;
?>
<br />
<?php if (!isset($is_avail)) { ?>
<h1 style="margin-top: 0px"><?php #echo $title ?></h1>
<p><?php echo $this->translate->_('Reporting period') ?>: <?php echo $reporting_period ?></p>
<?php
}

if (!$create_pdf && isset($graph_image_source) && $graph_image_source) { ?>
	<img src="<?php echo url::site() ?>trends/<?php echo $graph_image_source ?>" alt="" />
<?php } elseif ($create_pdf && isset($graph_chart_pdf_src) && $graph_chart_pdf_src) { ?>
	<img src="<?php echo $graph_chart_pdf_src ?>" alt="" />
<?php } ?>
<div style="clear:both"></div>
<?php if(isset($avail_template) && !empty($avail_template)) {
	echo $avail_template;
}
