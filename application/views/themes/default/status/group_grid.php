

			<!--<div class='statusTitle'>
				<?php //echo $label_header ?>
			</div>-->

<div class="widget left w98" id="status_group-grid" style="margin-top: -11px">
<?php
$i = 0;
foreach ($group_details as $details) {

?>

		<!--<div class="widget-header">
		<?php echo html::anchor('status/servicegroup/'.$details->group_name.'?style=detail', html::specialchars($details->group_name)) ?>
		(<?php echo html::anchor('extinfo/details/'.$details->group_type.'group/'.$details->group_name, html::specialchars($details->group_name)) ?>)
		</div>-->

	<table style="table-layout: fixed">
		<caption>
			<?php echo html::anchor('status/servicegroup/'.$details->group_name.'?style=detail', html::specialchars($details->group_name)) ?>
			(<?php echo html::anchor('extinfo/details/'.$details->group_type.'group/'.$details->group_name, html::specialchars($details->group_name)) ?>)
		</caption>
		<colgroup>
			<col style="width: 30px" />
			<col style="width: 200px" />
			<col style="width: 100%" />
			<col style="width: 30px" />
			<col style="width: 30px" />
			<col style="width: 30px" />
			<col style="width: 30px" />
			<col style="width: 30px" />
		</colgroup>
		<thead>
		<tr>
			<th class="no-sort"colspan="2"><?php echo $label_host ?></th>
			<th class="no-sort"><?php echo $label_services ?></th>
			<th class="no-sort" colspan="5"><?php echo $label_actions ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($details->hosts as $host) {
			$i++;
		?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
			<td class="icon bl">
				<?php
					if (!empty($host['icon_image'])) {
						echo html::anchor('extinfo/details/host/'.$host['host_name'], '<img src="'.$logos_path.$host['icon_image'].'" alt="'.$host['icon_image_alt'].'" title="'.$host['icon_image_alt'].'" style="width: 16px;" />',array('style' => 'border: 0px'));
					} ?>
			</td>
			<td style="white-space: normal"><?php echo html::anchor('extinfo/details/host/'.$host['host_name'], html::specialchars($host['host_name'])) ?></td>
			<td style="white-space: normal; line-height: 20px">
			<?php
				$tmp = 0;
				$j = 0;
				sort($details->services[$host['host_name']]);
				foreach	($details->services[$host['host_name']] as $service) {
						$search = array(0,1,2,3,4);
						$replace = array('ok','warning','unknown','critical','pending'); // rätt ?? dubbelkolla
						echo (($service['current_state'] != $tmp && $j != 0) ? '<br />' : '');
						echo (($service['current_state'] != $tmp || ($service['current_state'] == 0 && $j == 0)) ? html::image('/application/views/themes/default/images/icons/12x12/shield-'.strtolower(str_replace($search,$replace,$service['current_state'])).'.png', array('alt' => strtolower(str_replace($search,$replace,$service['current_state'])), 'title' => strtolower(str_replace($search,$replace,$service['current_state'])), 'style' => 'margin-bottom: -2px')).' &nbsp;' : '');
						$service_class = 'status'.Current_status_Model::status_text($service['current_state'], 'service');
						echo (($service['current_state'] != $tmp || $j == 0) ? '' : ', ').html::anchor('extinfo/details/service/'.$host['host_name'].'/?service='.$service['service_description'], $service['service_description'], array('class' => $service_class)).' ';
						if ($service['current_state'] != $tmp)
							$tmp = $service['current_state'];
						$j++;
				} # end each service ?>
			</td>
			<?php
				# also each host, under Actions
			?>
			<td class="icon">
				<?php echo html::anchor('extinfo/host/'.$host['host_name'], html::image($icon_path.'detail.gif', array('alt' => $label_host_extinfo, 'title' => $label_host_extinfo)), array('style' => 'border: 0px')) ?>
			</td>
			<td class="icon">
				<?php echo html::anchor('statusmap/host/'.$host['host_name'], html::image($icon_path.'status3.png', array('alt' => $label_status_map, 'title' => $label_status_map)), array('style' => 'border: 0px')); ?>
			</td>
			<td class="icon">
				<?php echo html::anchor('status/host/'.$host['host_name'], html::image($icon_path.'status2.gif', array('alt' => $label_service_status, 'title' => $label_service_status)), array('style' => 'border: 0px')) ?>
			</td>
			<td class="icon">
				<?php
				if (isset($pnp_path)) {
					echo '<a href="'.$pnp_path.'index.php?host='.$host['host_name'].'" style="border: 0px">'.html::image($icon_path.'graphlight.png', array('alt' => $label_pnp, 'title' => $label_pnp)).'</a>';
				} ?>
			</td>
			<td class="icon">
			<?php
				if (isset($nacoma_path)) {
					echo '<a href="'.$nacoma_path.'edit.php?obj_type=host&amp;host='.$host['host_name'].'" style="border: 0px">'.html::image($icon_path.'nacoma.png', array('alt' => $label_nacoma, 'title' => $label_nacoma)).'</a>';
				} ?>
			</td>
		</tr><?php
		}	# end each host ?>
		</tbody>
	</table>

<?php
}	# end each group
?>
</div>