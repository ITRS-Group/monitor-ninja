<div id="header" class="report-block">
	<div id="link_container" class="form-dropdown"></div>
	<div id="save_report_form" class="form-dropdown">
	<?php
		echo form::open(url::base(true).Router::$controller."/save");
		$report_name = $options['report_name'];
		unset($options['report_name']);
		echo $options->as_form();
		$options['report_name'] = $report_name;
		echo form::input(array("class" => "wide", "type" => "text", "name" => "report_name"), $report_name);
		echo form::input(array("class" => "save_report_btn", "type" => "submit"), _("Save report"));
		echo form::close();
?>
	</div>
	<h1><?php echo html::specialchars($title) ?></h1>
	<div class="report_options">
		<?php
		// $report_time_formatted is already escaped
		echo '<p>'._('Reporting period').': '.$report_time_formatted;
		echo (isset($str_start_date) && isset($str_end_date)) ? ' ('.html::specialchars($str_start_date).' '._('to').' '.html::specialchars($str_end_date).')' : '';
		echo '</p>';
		if ($type == 'avail' || $type == 'sla') {
			echo '<p>'.sprintf(_('Counting scheduled downtime as %s'), html::specialchars($options->get_value('scheduleddowntimeasuptime'))).'</p>';
		}
		if ($options['assumestatesduringnotrunning'])
			echo '<p>'.sprintf(_('Assuming previous state during program downtime')).'</p>';
		echo '<p>'.sprintf(_('Showing %s'), html::specialchars($options->get_value('state_types')));
		$states = array();
		if ($options['host_filter_status']) {
			foreach ($options->get_alternatives('host_filter_status') as $state => $name) {
				if (!isset($options['host_filter_status'][$state]))
					$states[] = $name;
				else if ($options['host_filter_status'][$state] != Reports_Model::HOST_EXCLUDED)
					$states[] = $name . ' as ' . Reports_Model::$host_states[$options['host_filter_status'][$state]];
			}
		}
		if ($options['service_filter_status']) {
			foreach ($options->get_alternatives('service_filter_status') as $state => $name) {
				if (!isset($options['service_filter_status'][$state]))
					$states[] = $name;
				else if ($options['service_filter_status'][$state] != Reports_Model::SERVICE_EXCLUDED)
					$states[] = $name . ' as ' . Reports_Model::$service_states[$options['service_filter_status'][$state]];
			}
		}
		if ($states) {
			// "unique" because undetermined is the same for hosts and services
			echo ' in ' . implode(', ', array_map(array('html', 'specialchars'), array_unique($states)));
		}
		echo '</p>';
		if ($type == 'sla')
			echo '<p>'.sprintf(_('Showing %s'), html::specialchars($options->get_value('sla_mode'))).'</p>';

?>
		<div class="description">
			<p><?php echo nl2br(html::specialchars(isset($description) ? $description : $options['description'])) ?></p>
		</div>

	</div>
</div>
