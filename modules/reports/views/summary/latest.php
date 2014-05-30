<?php defined('SYSPATH') OR die("No direct access allowed"); ?>
<div class="report-block">
<?php if (empty($result)) { ?>
<p><?php echo _('No log data recorded during this time') ?></p>
<?php } else { ?>
<?php echo isset($pagination)?$pagination:'' ?>
<div class="clear"></div>
<table>
	<tr>
		<th><?php //echo _('State'); ?></th>
		<th><?php echo _('Time'); ?></th>
		<th><?php echo _('Alert Types'); ?></th>
		<th><?php echo _('Host'); ?></th>
		<th><?php echo _('Service'); ?></th>
		<th><?php echo _('State Types'); ?></th>
		<th><?php echo _('Information'); ?></th>
	</tr>
	<?php
	$i = 0;
		$date_format = nagstat::date_format();
		foreach ($result as $ary) {
			$row = alert_history::get_user_friendly_representation($ary);
			$i++;
			echo '<tr class="'.($i%2 == 0 ? 'odd' : 'even').' eventrow" data-statecode="'.$ary['event_type'].'" data-timestamp="'.$ary['timestamp'].'" data-hostname="'.$ary['host_name'].'" data-servicename="'.$ary['service_description'].'">';
	?>
		<td class="icon status">
			<?php echo $row['image'] ?>
		</td>
		<td><?php echo date($date_format, $ary['timestamp']); ?></td>
		<td><?php echo $row['type']; ?></td>
		<td><?php echo $ary['host_name']?html::anchor(base_url::get().'extinfo/details/?type=host&host='.urlencode($ary['host_name']), $ary['host_name']):'' ?></td>
		<td><?php echo $ary['service_description']?html::anchor(base_url::get().'extinfo/details/?type=service&host='.urlencode($ary['host_name']).'&service='.$ary['service_description'], $ary['service_description']):'' ?></td>
		<td><?php echo $row['softorhard']; ?></td>
		<td>
<table class="output">
<tr><td><?php echo security::xss_clean($ary['output']);
		if ($ary['long_output'] !== NULL) {
			$long_output = trim($ary['long_output']);
			if (strlen($long_output) > 0) {
				echo "<span class='right'><button class='toggle-long-output'>";
				echo $options['include_long_output'] ? "-" : "+";
				echo "</button></span>";
				$hidden = $options['include_long_output'] ? "" : "style='display: none;'";
				echo "<span class='alert-history-long-output' " . $hidden . ">";
				echo '<br />'.nl2br(security::xss_clean($long_output));
				echo "</span>";
			}
		}
?>
</td><td style="border:0" class="comments">
<?php
		if (isset($ary['user_comment']))
			echo security::xss_clean($ary['user_comment']).'<br /><span class="author">/'.html::specialchars($ary['username']).'</span>';
		else
			echo '<img class="right" src="'.ninja::add_path('icons/16x16/add-comment.png').'"/>';
?>
</td></tr></table>
		</td>
	</tr>
	<?php } ?>
</table>
<?php echo isset($pagination)?$pagination:'' ?>
<?php } ?>
</div>
