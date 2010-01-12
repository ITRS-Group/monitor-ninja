<div id="header">
<h1><?php //echo $title; ?>There should be a customised header here but need to edit the controller first</h1>
	<p>
	<?php
	echo $label_report_period.': '.$report_time_formatted.' (';
	echo (isset($str_start_date) && isset($str_end_date)) ? $str_start_date.' '.$label_to.' '.$str_end_date : '';
	echo $use_average ? " <strong>(".$label_using_avg.")</strong>" : '';
	echo ')</p>';
	echo html::anchor(
		'#',
		html::image(
			$this->add_path('icons/32x32/square-print.png'),
			array(
				'alt' => $label_print,
				'title' => $label_print,
				'style' => 'position: absolute; top: 0px; right: 0px;'
			)
		)
	);
	echo isset($csv_link) ? $csv_link : '';
	echo isset($pdf_link) ? $pdf_link : ''; ?>
</div>
