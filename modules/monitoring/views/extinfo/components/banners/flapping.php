<?php

if ($object->get_flap_detection_enabled() && $object->get_is_flapping()) {
	$percent_change = number_format($object->get_percent_state_change(), 2);
	?>
	<li title="This object is switching between states at a high rate">
		<h2>is flapping</h2>
		<p class="faded"><?php echo $percent_change; ?>% state change</p>
	</li>
	<?php
}

