<div style="padding-top: 3em" class="clear"></div>
<?php if(count($synergy_events) == 0) { ?>
<h2><?php echo _('No Business Service Module events matching report criteria'); ?></h2>
<?php
return;
} ?>
<h2><?php echo _('Business Service Module events'); ?></h2>
<table>
	<thead>
		<tr>
			<th><?php echo _('Timestamp'); ?></th>
			<th><?php echo _('State'); ?></th>
			<th><?php echo _('BSM object'); ?></th>
			<th><?php echo _('Reason'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$even = false;
	$date_format = date::date_format();
	$states = array(
		0 => array('ok', _('OK')),
		1 => array('warning', _('Warning')),
		2 => array('critical', _('Critical')),
		3 => array('unknown', _('Unknown'))
	);
	$all_ok = _('All criteria fulfilled for an OK state');

	function sort_by_state_then_name($a, $b) {
		// Sort by state
		if (!isset($a['status'], $b['status'])) {
			return 0;
		}

		if ($a['status'] < $b['status']) {
			return +1;
		} else if ($a['status'] > $b['status']) {
			return -1;
		}

		if ($a['name'] < $b['name']) {
			return -1;
		} else if ($a['name'] > $b['name']) {
			return +1;
		}

		return 0;
	}

	function draw_json_tree($tree, $states, $indent_level = 1) {
		if(!isset($tree['items'])) {
			return null;
		}
		$info = null;
		uasort($tree['items'], "sort_by_state_then_name");
		foreach($tree['items'] as $node) {
			$info .= "<br>";
			if($indent_level) {
				// five spaces per indent was recently discovered to suffice forever
				$info .= str_repeat('&nbsp;', 5*($indent_level-1)).' &#8618; ';
			}
			$image = sprintf('<img alt="%1$s" title="%1$s" src="%2$s" />', $states[$node['status']][1], ninja::add_path('icons/16x16/shield-'.($states[$node['status']][0]).'.png'));
			if(!isset($node['items'])) {
				// this means we're in a leaf
				$msg = null;
				if(isset($node['result'])) {
					$msg = ": ".$node['result']['msg'];
				}
				$info .= $image.' '.$node['name'].$msg;
			} else {
				// this means we've got kids
				$info .= $image.' '.$node['name'].": ".$node['result']['msg'];
				$info .= draw_json_tree($node, $states, $indent_level + 1);
			}
		}
		return $info;
	}
	$log = op5log::instance('ninja');
	foreach($synergy_events as $event) {
		if($event->tree) {
			$json = json_decode($event->tree, true);
			if(!isset($json['result'])) {
				if($errno = json_last_error()) {
					$log->log("warning", "Invalid synergy report data (json decode error '$errno'): ".var_export($event->tree, true));
				}
				continue;
			}
			$info = "<strong>".$json['result']['msg']."</strong>";
			$info .= draw_json_tree($json, $states);
		} else {
			$info = $all_ok;
		} ?>
		<tr class="<?php if($even) { $even = false; echo 'even'; } else { $even = true; echo 'odd'; } ?>">
			<td><?php echo date($date_format, $event->timestamp); ?></td>
			<td><?php printf('<img alt="%1$s" title="%1$s" src="%2$s" />', $states[$event->state][1], ninja::add_path('icons/16x16/shield-'.($states[$event->state][0]).'.png'));?></td>
			<td><?php echo $event->service_description; ?> (saved as service on host <?php echo $event->host_name; ?>)</td>
			<td><?php echo $info; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
