<?php defined('SYSPATH') OR die("No direct access allowed"); ?>
<div class="report-block">
<?php echo isset($pagination)?$pagination:'' ?>
<div class="clear"></div>
<table>
	<tr>
		<th class="headerNone "><?php //echo _('State'); ?></th>
		<th class="headerNone "><?php echo _('Time'); ?></th>
		<th class="headerNone "><?php echo _('Alert Types'); ?></th>
		<th class="headerNone "><?php echo _('Host'); ?></th>
		<th class="headerNone "><?php echo _('Service'); ?></th>
		<th class="headerNone "><?php echo _('State Types'); ?></th>
		<th class="headerNone "><?php echo _('Information'); ?></th>
	</tr>
	<?php
	$i = 0;
	if (!empty($result)) {
		foreach ($result as $ary) {
			$row = alert_history::get_user_friendly_representation($ary);
			$i++;
			echo '<tr class="'.($i%2 == 0 ? 'odd' : 'even').' eventrow" data-statecode="'.$ary['event_type'].'" data-timestamp="'.$ary['timestamp'].'" data-hostname="'.$ary['host_name'].'" data-servicename="'.$ary['service_description'].'">';
	?>
		<td class="icon status">
			<?php echo $row['image'] ?>
		</td>
		<td><?php echo date(nagstat::date_format(), $ary['timestamp']); ?></td>
		<td><?php echo $row['type']; ?></td>
		<td><?php echo $ary['host_name']?html::anchor(base_url::get().'extinfo/details/?type=host&host='.urlencode($ary['host_name']), $ary['host_name']):'' ?></td>
		<td><?php echo $ary['service_description']?html::anchor(base_url::get().'extinfo/details/?type=service&host='.urlencode($ary['host_name']).'&service='.$ary['service_description'], $ary['service_description']):'' ?></td>
		<td><?php echo $row['softorhard']; ?></td>
		<td>
<table class="output">
<tr><td><?php echo htmlspecialchars($ary['output']); ?></td><td style="border:0" class="comments">
		<?php if (isset($ary['user_comment']))
			echo $ary['user_comment'].'<br /><span class="author">/'.$ary['username'].'</span>';
		else
			echo '<img src="'.ninja::add_path('icons/16x16/add-comment.png').'"/>'
		?>
</td></tr></table>
		</td>
	</tr>
	<?php }
	} ?>
</table>
<?php echo isset($pagination)?$pagination:'' ?>
</div>
