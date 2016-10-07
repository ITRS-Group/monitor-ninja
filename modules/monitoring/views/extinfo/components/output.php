<div class="information-component">
	<div class="information-component-title">Output</div>
	<p class="output">
	<?php
		$output = $object->get_plugin_output();
		$long_plugin_output = $object->get_long_plugin_output();
		if ($long_plugin_output) {
			$output .= '<br />' . nl2br($long_plugin_output);
		}
		if (strlen($output)) {
			echo security::xss_clean($output);
		} else {
			?>
				<span class="faded">No output from plugin...</span>
			<?php
		}
	?>
	</p>
</div>
