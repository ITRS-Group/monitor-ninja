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
	$date_format = nagstat::date_format();
	// sprintf into this with: code, label
	$image = '<img alt="%2$s" title="%2$s" src="'.ninja::add_path('icons/16x16/shield-%1$s.png').'" />';
	$states = array(
		0 => array('ok', _('OK')),
		1 => array('warning', _('Warning')),
		2 => array('critical', _('Critical')),
		3 => array('unknown', _('Unknown'))
	);
	$all_ok = _('All criteria fulfilled for an OK state');

	function draw_json_tree($tree, $image, $states, $indent_level = 1) {
		if(!isset($tree->items)) {
			return null;
		}
		$info = null;
		foreach($tree->items as $node_name => $node) {
			$info .= "<br>";
			if($indent_level) {
				// five spaces per indent was recently discovered to suffice forever
				$info .= str_repeat('&nbsp;', 5*($indent_level-1)).' &#8618; ';
			}
			if(isset($node->status)) {
				// this means we're in a leaf
				$msg = null;
				if(isset($node->result)) {
					$msg = ": ".$node->result->msg;
				}
				$info .= sprintf($image, $states[$node->status][0], $states[$node->status][1]).' '.$node_name.$msg;
			} else {
				// this means we've got kids
				$info .= sprintf($image, $states[$node->result->status][0], $states[$node->result->status][1]).' '.$node_name.": ".$node->result->msg;
				$info .= draw_json_tree($node, $image, $states, $indent_level + 1);
			}
		}
		return $info;
	}
	$log = op5log::instance('ninja');
	foreach($synergy_events as $event) {
		if($event->tree) {
			$json = json_decode($event->tree);
			if(!isset($json->result)) {
				if($errno = json_last_error()) {
					$log->log("warning", "Invalid synergy report data (json decode error '$errno'): ".var_export($event->tree, true));
				}
				continue;
			}
			$info = "<strong>".$json->result->msg."</strong>";
			$info .= draw_json_tree($json, $image, $states);
		} else {
			$info = $all_ok;
		} ?>
		<tr class="<?php if($even) { $even = false; echo 'even'; } else { $even = true; echo 'odd'; } ?>">
			<td><?php echo date($date_format, $event->timestamp); ?></td>
			<td><?php printf($image, $states[$event->state][0], $states[$event->state][1]); ?></td>
			<td><?php echo $event->service_description; ?> (saved as service on host <?php echo $event->host_name; ?>)</td>
			<td><?php echo $info; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
