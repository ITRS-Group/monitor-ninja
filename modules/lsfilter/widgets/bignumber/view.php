<?php defined('SYSPATH') OR die('No direct access allowed.');

if($error_msg) {
	echo "<p class='alert error'>".html::specialchars($error_msg)."</p>";
	return;
}
?>
<a href="<?php echo listview::querylink($query); ?>" title="Go to listview">
	<div class="<?php echo $state; ?> state-background state_summary-container">
		<div class="state_summary-state">
			<span class="big-number"><?php echo $display_text; ?></span>
		</div>
	</div>
</a>
