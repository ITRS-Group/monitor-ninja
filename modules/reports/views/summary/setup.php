<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"><?php
	if (isset($error_msg)) {
	echo '<ul class="alert error"><li>'.$error_msg.'</li></ul>';
	}
?></div>

<div class="report-page-setup">
	<?php echo new View('reports/saveselector', array('saved_reports' => $saved_reports, 'scheduled_info' => $scheduled_info)); ?>

	<h2><?php echo _('Report Mode') ?></h2>
	<hr />
	<form id="report_mode_form"><br />
	<label><?php echo form::radio(array('name' => 'report_mode'), 'standard', !$options['report_type'] || $options['standardreport']); ?> <?php echo _('Standard') ?></label> &nbsp; &nbsp; <label><?php echo form::radio(array('name' => 'report_mode'), 'custom', $options['report_type'] && !$options['standardreport']); ?> <?php echo _('Custom') ?></label>
	</form>
<br />

	<?php echo $report_options ?>
</div>
