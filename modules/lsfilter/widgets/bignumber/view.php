<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<a href="<?php echo $link; ?>" title="Go to listview" target="_blank">
	<div class="<?php echo $state; ?> state-background state_summary-container">
		<?php if($display_explanation) { ?>
		<p class="explanation"><?php echo html::specialchars($display_explanation); ?></p>
		<?php } elseif($perf_data) { ?>
		<div class="state_summary-state">
			<span class="big-number"><?php echo html::specialchars($perf_data['value']); ?><?php echo isset($perf_data['unit']) ? html::specialchars($perf_data['unit']) : ''; ?></span>
		</div>
		<?php } else { ?>
		<div class="state_summary-state">
			<span class="big-number"><?php echo html::specialchars($display_text); ?></span>
		</div>
		<?php } ?>
	</div>
</a>
