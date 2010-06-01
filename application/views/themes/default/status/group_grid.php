<?php defined('SYSPATH') OR die('No direct access allowed.');
$t = $this->translate; ?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
	<div class="widget left w32" id="page_links">
		<ul>
		<li><?php echo $this->translate->_('View').', '.$label_view_for.':'; ?></li>
		<?php
		if (isset($page_links)) {
			foreach ($page_links as $label => $link) {
				?>
				<li><?php echo html::anchor($link, $label) ?></li>
				<?php
			}
		}
		?>
		</ul>
	</div>

	<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
	?>

	<div id="filters" class="left">
	<?php
	if (isset($filters) && !empty($filters)) {
		echo $filters;
	}
	?>
	</div>
</div>

<div class="widget left w98" id="status_group-grid">
<?php
if (!empty($group_details))
	foreach ($group_details as $details) {
?>

	<table class="group_grid_table">
		<caption>
			<?php echo html::anchor('status/'.$grouptype.'group/'.$details->group_name.'?style=detail', html::specialchars($details->group_alias)) ?>
			(<?php echo html::anchor('extinfo/details/'.$details->group_type.'group/'.$details->group_name, html::specialchars($details->group_name)) ?>)
			<?php if (nacoma::link()===true)
				echo nacoma::link('configuration/configure/'.$grouptype.'group/'.urlencode($details->group_name), 'icons/16x16/nacoma.png', sprintf($this->translate->_('Configure this %sgroup'), $grouptype));?>
		</caption>
		<thead>
		<tr>
			<th class="no-sort"colspan="2"><?php echo $label_host ?></th>
			<th class="no-sort"><?php echo $label_services ?></th>
			<th class="no-sort"><?php echo $label_actions ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$i = 0;
		foreach ($details->hosts as $host) {
			$i++;
		?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
			<td class="icon bl">
				<?php
					if (!empty($host['icon_image'])) {
						echo html::anchor('extinfo/details/host/'.$host['host_name'], html::image('application/media/images/logos/'.$host['icon_image'], array('style' => 'height: 16px; width: 16px', 'alt' => $host['icon_image_alt'], 'title' => $host['icon_image_alt'])),array('style' => 'border: 0px'));
					} ?>
			</td>
			<td style="white-space: normal; width: 180px"><?php echo html::anchor('extinfo/details/host/'.$host['host_name'], html::specialchars($host['host_name'])) ?></td>
			<td style="white-space: normal; line-height: 20px">
			<?php
				$tmp = 0;
				$j = 0;
				sort($details->services[$host['host_name']]);
				foreach	($details->services[$host['host_name']] as $service) {
						$search = array(Current_status_Model::SERVICE_OK, Current_status_Model::SERVICE_WARNING, Current_status_Model::SERVICE_CRITICAL, Current_status_Model::SERVICE_UNKNOWN, Current_status_Model::SERVICE_PENDING);
						$replace = array('ok','warning','critical','unknown','pending'); // rätt ?? dubbelkolla
						echo (($service['current_state'] != $tmp && $j != 0) ? '<br />' : '');
						echo (($service['current_state'] != $tmp || ($service['current_state'] == 0 && $j == 0)) ? html::image($this->add_path('icons/12x12/shield-'.strtolower(str_replace($search,$replace,$service['current_state'])).'.png'), array('alt' => strtolower(str_replace($search,$replace,$service['current_state'])), 'title' => strtolower(str_replace($search,$replace,$service['current_state'])), 'style' => 'margin-bottom: -2px')).' &nbsp;' : '');
						$service_class = 'status'.Current_status_Model::status_text($service['current_state'], 'service');
						echo (($service['current_state'] != $tmp || $j == 0) ? '' : ', ').html::anchor('extinfo/details/service/'.$host['host_name'].'/?service='.$service['service_description'], $service['service_description'], array('class' => $service_class));
						if ($service['current_state'] != $tmp)
							$tmp = $service['current_state'];
						$j++;
				} # end each service ?>
			</td>
			<?php
				# also each host, under Actions
			?>
			<td style="text-align: left; width: 133px">
				<?php
					if (isset($nacoma_path))
						echo html::anchor('configuration/configure/host/'.$host['host_name'], html::image($icon_path.'nacoma.png', array('alt' => $label_nacoma, 'title' => $label_nacoma)),array('style' => 'border: 0px')).'&nbsp;';
					if (isset($pnp_path) && pnp::has_graph($host['host_name']))
						echo '<a href="'.$pnp_path.'host='.$host['host_name'].'" style="border: 0px">'.html::image($icon_path.'pnp.png', array('alt' => $label_pnp, 'title' => $label_pnp)).'</a>&nbsp;';
					echo html::anchor('extinfo/details/host/'.$host['host_name'], html::image($icon_path.'extended-information.gif', array('alt' => $label_host_extinfo, 'title' => $label_host_extinfo)), array('style' => 'border: 0px')).'&nbsp;';
					echo html::anchor('statusmap/host/'.$host['host_name'], html::image($icon_path.'locate-host-on-map.png', array('alt' => $label_status_map, 'title' => $label_status_map)), array('style' => 'border: 0px')).'&nbsp;';
					echo html::anchor('status/host/'.$host['host_name'], html::image($icon_path.'service-details.gif', array('alt' => $label_service_status, 'title' => $label_service_status)), array('style' => 'border: 0px')).'&nbsp;';
					if (!empty($host['action_url'])) {
						echo '<a href="'.nagstat::process_macros($host['action_url'], $host).'" style="border: 0px" target="_blank">';
						echo html::image($this->add_path('icons/16x16/host-actions.png'), array('alt' => $t->_('Perform extra host actions'), 'title' => $t->_('Perform extra host actions')));
						echo '</a>&nbsp;';
					}
					if (!empty($host['notes_url'])) {
						echo '<a href="'.nagstat::process_macros($host['notes_url'], $host).'" style="border: 0px" target="_blank">';
						echo html::image($this->add_path('icons/16x16/host-notes.png'), array('alt' => $t->_('View extra host notes'), 'title' => $t->_('View extra host notes')));
						echo '</a>';
					}

				?>
			</td>
		</tr><?php
		}	# end each host ?>
		</tbody>
	</table>

<?php
}	# end each group
else { ?>
	<table class="group_grid_table">
		<thead>
		<tr>
			<th class="no-sort"><?php echo $label_host ?></th>
			<th class="no-sort"><?php echo $label_services ?></th>
			<th class="no-sort"><?php echo $label_actions ?></th>
		</tr>
		</thead>
		<tbody>
		<tr class="even">
			<td colspan="3"><?php echo $error_message ?></td>
		</tr>
		</tbody>
	</table>
	<?php
}
?>
</div>