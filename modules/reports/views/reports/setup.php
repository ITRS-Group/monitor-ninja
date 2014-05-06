<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"><?php
	if (isset($error_msg)) {
		echo '<ul class="alert error"><li>'.$error_msg.'</li></ul>';
	}
?></div>

<div class="report-page-setup">
		<div class="setup-table">
			<?php echo new View('reports/saveselector', array('saved_reports' => $saved_reports, 'scheduled_info' => $scheduled_info)); ?>
		</div>

		<?php echo form::open($type.'/generate', array('class' => 'report_form')); ?>
			<?php echo $report_options; ?>
		</form>
	</div>
</div>
