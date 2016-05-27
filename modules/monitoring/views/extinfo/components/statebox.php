<div class="left width-20 information-state-box <?php echo $object->get_state_text();?> state-background">
  <div class="extinfo-state">
    <?php

	$icons = array(
		'ok' => '⊙',
		'up' => '⊙',
		'warning' => '⊘',
		'down' => '⊗',
		'critical' => '⊗',
		'unreachable' => '⊚',
		'unknown' => '⊚',
		'pending' => '⊝'
	);

    echo '<div class="information-state-box-blob ' . $object->get_state_text() . '">' . $icons[$object->get_state_text()] . '</div>';
	echo '<div class="information-state-box-block">';
	if ($object->get_table() === 'hosts') {
        echo '<div class="information-state-box-name">' . $object->get_name() . '</div>';
    } else {
        echo '<div class="information-state-box-name">' . $object->get_description() . '</div>';
        echo '<p>on <a href="' . $linkprovider->get_url('extinfo', 'details', array('host' => $object->get_host()->get_name())) . '">' . $object->get_host()->get_name() . '</a></p>';
	}
	echo '</div>';

	if ($object->get_state_type_text() === 'soft') {
		echo '<div title="This object has this preliminary state but may change after all other check attempts" class="information-state-box-block">';
        echo '<p class="faded">Soft</p>';
        echo '<div class="information-state-box-state">' . $object->get_state_text() . '</div>';
		echo '<p class="faded">after '.$object->get_current_attempt().' out of '.$object->get_max_check_attempts().' check attempts</p>';
		echo '</div>';
    } else {
		echo '<div title="This object was given this state after all check attempts were exhausted" class="information-state-box-block">';
        echo '<p class="faded">Hard</p>';
		echo '<div class="information-state-box-state">' . $object->get_state_text() . '</div>';
		echo '</div>';
    }

    ?>

    <ul class="information-state-box-more">
<?php

	$in_downtime = $object->get_scheduled_downtime_depth();
	$host_in_downtime = false;
	$host = $object;
	$service = false;

	if ($object->get_table() == 'services' && $object->get_host()->get_scheduled_downtime_depth() ) {
		$in_downtime = true;
		$host_in_downtime = true;
		$host = $object->get_host();
	} else if ($object->get_table() == 'services') {
		$service = true;
	}

	if ($in_downtime) {

		$title = "This host is in scheduled downtime, click here to go to a list of the relevant downtimes";
		$label = "in scheduled downtime";
		if($host_in_downtime) {
			$title = "The host this service resides on is in scheduled downtime, click here to go to a list of the relevant downtimes";
			 $label = "host in scheduled downtime";
		} else if ($service) {
			$title = "This service is in scheduled downtime, click here to go to a list of the relevant downtimes";
			$label = "in scheduled downtime";
		}

		echo "<li title='This host is in scheduled downtime, click here to go to a list of the relevant downtimes'><h2>";
		if ($service) {
			echo "<a href='" .listview::querylink('[downtimes] host.name="' . $object->get_host()->get_name() . '" and service.description = "' . $object->get_description() . '"'). "'>";
		} else {
			echo "<a href='" .listview::querylink('[downtimes] host.name="' . $host->get_name() . '"'). "'>";
		}
		echo $label;
		echo '</a></h2></li>';
	}

	$flap_value = $object->get_flap_detection_enabled() && $object->get_is_flapping() ? 'Yes' : 'No';
	$percent_state_change_str = '('.number_format((int)$object->get_percent_state_change(), 2).'% '._('state change').')';

	if ($object->get_is_flapping()) {
		echo '<li title="This object is switching between states at a high rate"><h2>is flapping</h2><p class="faded">'. $percent_state_change_str . '</p></li>';
	}
?>
		<?php
			$groups = $object->get_groups();
			$group_table = ($object->get_table() === 'services') ? 'servicegroups' : 'hostgroups';
			if (count($groups)) {
				echo '<li>';
				echo "<h3>Member of:</h3>";
				foreach ($groups as $group) {
					echo "<p><a href='" . listview::querylink('[' . $group_table . '] name="' . $group . '"') . "'>$group</a></p>";
				}
				echo '</li>';
			}
		?>
	</ul>
  </div>
</div>
