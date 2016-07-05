<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<a href="<?php echo $link; ?>" title="Go to listview" target="_blank">
	<div class="<?php echo $state; ?> state-background state_summary-container">
		<?php if($display_explanation) { ?>
		<p class="explanation"><?php echo html::specialchars($display_explanation); ?></p>
		<?php } elseif($perf_data) { ?>
		<div class="state_summary-state">
			<span class="big-number">
				<?php
					$length = strlen((string)$perf_data['value']);
					$fontsize = 120 - ($length * 5);

					if ($fontsize > 100) $fontsize = 100;
					elseif ($fontsize < 60) $fontsize = 60;
				?>
					<span class="big-number-perfdata-value" style="font-size: <?php echo $fontsize; ?>%; line-height: <?php echo $fontsize; ?>%">

						<?php echo html::specialchars($perf_data['value']); ?>
				</span>
					<span class="big-number-perfdata-uom" style="line-height: <?php echo $fontsize; ?>% ">
					<?php echo isset($perf_data['unit']) ? html::specialchars($perf_data['unit']) : ''; ?>
				</span>
			</span>
		</div>
		<?php } else { ?>
		<div class="state_summary-state">
			<span class="big-number"><?php echo html::specialchars($display_text); ?></span>
		</div>
		<?php } ?>
	</div>
</a>
