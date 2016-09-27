<?php

if ($object->get_state() > 0) {
	if ($object->get_acknowledged_problem()) {
?>
<li title="This problem has been acknowledged">
	<h2>
		<?php echo icon::get('check'); ?>
		acknowledged
	</h2>
</li>
<?php
	} else {
		$linkprovider = LinkProvider::factory();
		$href = $linkprovider->get_url('cmd', null, array(
			'command' => 'acknowledge_problem',
			'table' => $object->get_table(),
			'object' => $object->get_key()
		));
?>
<li title="This problem is not yet acknowledged, click to acknowledge!">
	<h2 class="faded">
		<?php echo icon::get_linked('check-empty', $href); ?>
		<a class="command-ajax-link" href="<?php echo $href; ?>">acknowledge</a>
	</h2>
</li>
<?php
	}
}
