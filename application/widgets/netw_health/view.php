<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm" id="widget-network_health">
	<div class="widget-header">
		<strong>Network health</strong>
	</div>
	<div class="widget-editbox">
		Edit the widget here
	</div>
	<div class="widget-content">
		This is widget content:<br />
		<?php echo $test ?><br />
<?php
	if (!empty($arguments)) {
		foreach ($arguments as $arg) {
			echo $arg."<br />";
		}
	}
?>
	</div>
</div>

