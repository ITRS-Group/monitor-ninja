<div class="state_summary-container">
<?php
foreach($state_definitions['states'] as $silly_counter => $state) {

	$css_class = call_user_func(
		$state['css_class'],
		$stats[$silly_counter]
	);

	printf("<a title=\"Go to the listview for these " . $stats[$silly_counter] . " $object_type\" href=\"%s\"><div class='state_summary-state state_summary-%s'>
		<span class='" . strtolower($state['label']) . " state_summary-figure'>%s</span><br>
		<span class='" . strtolower($state['label']) . " supplementary state_summary-description'>%s</span>
		</div></a>",
		listview::querylink($queries[$silly_counter]),
		$css_class,
		text::clipped_number($stats[$silly_counter], 2),
		$state['label']
	);

}
?>
</div>
