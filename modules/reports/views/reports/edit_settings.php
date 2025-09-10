<div id="response"></div>
<div id="progress"></div>
<div id="options">
	<?php echo form::open($type.'/generate', array('class' => 'report_form'));?>
	<?php
	if ($report_options instanceof View) {
		$report_options->render(true);
	} else {
		echo htmlspecialchars($report_options);
	}
	?>
	</form>
</div>
