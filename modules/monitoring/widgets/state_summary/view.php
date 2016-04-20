<?php

$prefix = 'state_summary';

// This widget is supposed to add color for small systems. With that in mind,
// colorfulness > precise data. Ceil the number of figures that appears.
$format_number = function($count) {
	if($count < 100) {
		return $count;
	}
	return "99+";
};
?>
<div class="state_summary-container">
<?php
foreach($state_definitions['states'] as $silly_counter => $state) {

	$css_class = call_user_func(
		$state['css_class'],
		$stats[$silly_counter]
	);

	printf("<a href=\"%s\"><div class='$prefix-state $prefix-%s'>
		<span class='$prefix-figure'>%s</span><br>
		<span class='$prefix-description'>%s</span>
		</div></a>",
		listview::querylink($queries[$silly_counter]),
		$css_class,
		$format_number($stats[$silly_counter]),
		$state['label']
	);

}
?>
</div>
