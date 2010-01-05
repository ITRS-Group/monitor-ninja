<div id="header">
	<h3><?php echo $label_report_period ?>: <?php echo $report_time_formatted; ?></h3>
	<?php if (isset($str_start_date) && isset($str_end_date) ) {
		 echo $str_start_date.' '.$label_to.' '.$str_end_date;
	}
	echo $use_average ? " <strong>(".$label_using_avg.")</strong>" : '';
	echo html::anchor(
		'#',
		html::image(
			$this->add_path('icons/32x32/square-print.png'),
			array(
				'alt' => $label_print,
				'title' => $label_print,
				'style' => 'position: absolute; top: 10px; left: 680px;'
			)
		)
	);
	echo isset($csv_link) ? $csv_link : '';
	echo isset($pdf_link) ? $pdf_link : ''; ?>
</div>
