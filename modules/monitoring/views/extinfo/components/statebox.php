<div class="left width-20 information-state-box <?php echo $object->get_state_text();?> state-background">
  <div class="extinfo-state">
    <?php

	echo '<div class="information-state-box-blob ' . $object->get_state_text() . '">' .
		icon::get('state-' . $object->get_state_text()) .
		'</div>';
	echo '<div class="information-state-box-block">';
	if ($object->get_table() === 'hosts') {
		echo '<div class="information-state-box-name">' . $object->get_name();
		echo icon::get_linked('cog', $linkprovider->get_url('cmd', null, array(
			'command' => 'get_config_url',
			'object' => $object->get_key(),
			'table' => $object->get_table()
		)), "Configure this host");
		echo "</div>";
    } else {
        echo '<div class="information-state-box-name">' . $object->get_description();
		echo icon::get_linked('cog', $linkprovider->get_url('cmd', null, array(
			'command' => 'get_config_url',
			'object' => $object->get_key(),
			'table' => $object->get_table()
		)), "Configure this service");
		echo "</div>";
        echo '<p>on <a title="Go to the host of this service" href="' . $linkprovider->get_url('extinfo', 'details', array('host' => $object->get_host()->get_name())) . '">' . $object->get_host()->get_name() . '</a></p>';
	}
	echo '</div>';

	if ($object->get_state_type_text() === 'soft') {
?>
	<div class="information-state-box-block" title="This object has this preliminary state but may change after all check attempts have been exhausted">
		<p class="faded">Soft</p>
		<div class="information-state-box-state">
			<?php echo $object->get_state_text(); ?>
		</div>
		<p class="faded">
			after <?php echo $object->get_current_attempt(); ?> out of <?php echo $object->get_max_check_attempts(); ?> check attempts
		</p>
	</div>
<?php
	} else {
?>
	<div class="information-state-box-block" title="This object was given this state after all check attempt were exhausted">
		<p class="faded">Hard</p>
		<div class="information-state-box-state">
			<?php echo $object->get_state_text(); ?>
		</div>
		<p>
		<a href="<?php echo $linkprovider->get_url('cmd', 'index', array('command' => 'check_now', 'object' => $object->get_key(), 'table' => $object->get_table())); ?>" title="Click to schedule a new check as soon as possible">Check now</a>
		</p>
	</div>
<?php
    }

    ?>

    <ul class="information-state-box-more">
<?php

	View::factory('extinfo/components/banners/acknowledged', array(
		'object' => $object,
		'linkprovider' => $linkprovider
	))->render(true);

	View::factory('extinfo/components/banners/scheduled_downtime', array(
		'object' => $object,
		'linkprovider' => $linkprovider
	))->render(true);

	View::factory('extinfo/components/banners/flapping', array(
		'object' => $object,
		'linkprovider' => $linkprovider
	))->render(true);

	View::factory('extinfo/components/banners/membership', array(
		'object' => $object,
		'linkprovider' => $linkprovider
	))->render(true);

?>
	</ul>
  </div>
</div>
