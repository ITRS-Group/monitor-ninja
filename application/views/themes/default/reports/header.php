<div id="header">
	<h1 style="margin-top: 0px !important;"><?php echo isset($title) ? $title : _('SLA Breakdown'); ?></h1>
	<p><?php echo _('Reporting period').': '.$report_time_formatted; ?>
	<?php echo (isset($str_start_date) && isset($str_end_date)) ? ' ('.$str_start_date.' '._('to').' '.$str_end_date.')' : '';
	if ($use_average) echo " <strong>("._('using averages').")</strong>"; ?>
	</p><?php
	if (!$create_pdf) {
		echo html::anchor(
			'#',
			html::image(
				$this->add_path('icons/32x32/square-print.png'),
				array(
					'alt' => _('Print report'),
					'title' => _('Print report'),
					'style' => 'position: absolute; top: 16px; right: 0px;',
					'onclick' => 'window.print()'
				)
			)
		);
	}
	echo isset($csv_link) ? $csv_link : '';
	echo isset($pdf_link) ? $pdf_link : '';

?>
</div>
