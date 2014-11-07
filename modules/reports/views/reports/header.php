<div id="header" class="report-block">
	<div class="report-links">
		<?php
		if (!isset($skip_csv)) {
			echo "<span class='image-link'></span>";
			echo form::open($type.'/generate');
			echo $options->as_form();
			echo '<input type="hidden" name="output_format" value="csv" />';
			$csv_alt = _('Download report as CSV');
			echo "<input type='image' src='".$this->add_path('icons/32x32/page-csv.png')."' alt='".$csv_alt."' title='".$csv_alt."'/>";
			echo "</form>\n";
		}

		if (!isset($skip_pdf)) {
			echo "<span class='image-link'></span>";
			echo form::open($type.'/generate');
			echo $options->as_form();
			echo '<input type="hidden" name="output_format" value="pdf" />';
			$pdf_alt = _('Show as pdf');
			echo '<input type="image" src="'.$this->add_path('icons/32x32/page-pdf.png').'" title="'.$pdf_alt.'" alt="'.$pdf_alt.'" />';
			echo '</form>';
		}

		if (!isset($skip_save)) { ?>
		<a class="image-link" href="#" id="save_report"><?php echo html::image($this->add_path('/icons/32x32/square-save.png'), array('alt' => _('Save report'), 'title' => _('Save report'))); ?></a>
		<?php
		}
		if (!isset($skip_edit)) { ?>
		<a class="image-link fancybox" href="#options"><?php echo html::image($this->add_path('/icons/32x32/square-edit.png'), array('alt' => _('Edit settings'), 'title' => _('Edit settings'))); ?></a>
		<?php } ?>
		<?php if ($options['report_id']) { ?>
		<a class="image-link" id="show_schedule" href="<?php echo url::base(true) ?>schedule/show"><?php echo html::image($this->add_path('/icons/32x32/square-view-schedule.png'), array('alt' => _('View schedule'), 'title' => _('View schedule'))); ?></a>
		<?php }
			# make it possible to get the link (GET) to the current report
			echo html::anchor($type.'/generate?'.$options->as_keyval_string(),
				html::image($this->add_path('/icons/32x32/square-link.png'),
					array('alt' => '','title' => _('Direct link'))),
				array('class' => 'image-link', 'id' => 'current_report_params', 'title' => _('Direct link to this report. Right click to copy or click to view.'))
			);
		?>
	</div>
	<div id="link_container" class="form-dropdown"></div>
	<div id="save_report_form" class="form-dropdown">
		<form method="post" action="<?php echo url::base(true).Router::$controller."/save" ?>">
			<?php
			$report_name = $options['report_name'];
			unset($options['report_name']);
			echo $options->as_form();
			echo '<input class="wide" type="text" name="report_name" value="'.$report_name.'" />';
			$options['report_name'] = $report_name;
			?>
			<input type="submit" class="save_report_btn" value="<?php echo _('Save report') ?>" />
		</form>
	</div>
	<h1><?php echo $title ?></h1>
	<div class="report_options">
	<?php
		echo '<p>'._('Reporting period').': '.$report_time_formatted;
		echo (isset($str_start_date) && isset($str_end_date)) ? ' ('.$str_start_date.' '._('to').' '.$str_end_date.')' : '';
		echo '</p>';
		if ($type == 'avail' || $type == 'sla') {
			echo '<p>'.reports::get_included_states($options['report_type'], $options).'</p>';
			echo '<p>'.sprintf(_('Counting scheduled downtime as %s'), $options->get_value('scheduleddowntimeasuptime')).'</p>';
		}
		if ($options['assumestatesduringnotrunning'])
			echo '<p>'.sprintf(_('Assuming previous state during program downtime')).'</p>';
		if ($this->type == 'summary' || $options['include_alerts'] || $options['include_summary']) {
					echo '<p>'.sprintf(_('Showing alerts for %s and %s, %s'), $options->get_value('host_states'), $options->get_value('service_states'), $options->get_value('state_types')).'</p>';
		}
		if ($this->type == 'sla')
			echo '<p>'.sprintf(_('Showing %s'), $options->get_value('sla_mode')).'</p>';

?>
		<div class="description">
			<p><?php echo nl2br(html::specialchars(isset($description) ? $description : $options['description'])) ?></p>
		</div>

	</div>
</div>
