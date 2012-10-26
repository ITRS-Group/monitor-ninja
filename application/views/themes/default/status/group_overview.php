<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
<div id="page_links">
	<em class="page-links-label"><?php echo _('View').', '.$label_view_for.':'; ?></em>
	<ul>
	<?php
	if (isset($page_links)) {
		foreach ($page_links as $label => $link) {
			?>
			<li><a href="<?php echo url::base(true) . $link ?>"><?php echo $label ?></a></li>
			<?php
		}
	}
	?>
	</ul>
</div>
<div class="clear"></div>
<hr />

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
    <div class="clear"> </div>
</div>

<div id="status_group-overview">
<?php
	$pnp4nagios_path = Kohana::config('config.pnp4nagios_path');
	$nacoma_link = nacoma::link();
	if ($nacoma_link===true)
		echo sprintf(_('Add new %sgroup'), ucfirst($grouptype)).': &nbsp;'.nacoma::link('configuration/configure/'.$grouptype.'group/', 'icons/16x16/nacoma.png', sprintf(_('Add new %sgroup'), $grouptype));
	$j = 0;
	echo (isset($pagination)) ? $pagination : '';
?>
	<form action="<?php echo url::base(true) ?>command/multi_action" method="post">
	<?php
	# make sure we have something to iterate over
	$check = false;
	$i = 0;
	?>
	<table class="group_overview_table">
		<caption>
			<img src="<?php echo ninja::add_path('icons/16x16/check-boxes.png') ?>" style="margin-bottom: -3px" /> <a href="#" id="select_multiple_items"><?php echo _('Select Multiple Items') ?></a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo url::base(true).'status/'.$grouptype.'group/'.$group_name.'?style=detail' ?>"><?php echo htmlspecialchars($group_alias) ?></a>
			<?php /*Comment this out for now. Might not be used for anything anymore ?>
			<span>(<a href="<?php echo url::base(true).'extinfo/details?type='.$grouptype.'group&amp;host='.$group_name ?>"><?php echo $group_name ?></a>)</span>
		<?php */ 
		if ($nacoma_link===true)
			echo nacoma::link('configuration/configure/?type='.$grouptype.'group&amp;name='.urlencode($group_name), 'icons/16x16/nacoma.png', sprintf(_('Configure this %sgroup'), $grouptype));?>
	</caption>
		<tr>
			<th><em><?php echo _('Status');?></em></th>
			<th class="item_select"><input type="checkbox" class="select_group_items" title="Click to select/deselect group" value="<?php echo $j; ?>" /></th>
			<th colspan="2"><?php echo $lable_host ?></th>
			<th class="no-sort"><?php echo $lable_services ?></th>
			<th class="no-sort"><?php echo $lable_actions ?></th>
		</tr>
		<?php
		$i=0; foreach ($host_details as $host ) { $i++;
			$host = (object) $host;
			$host_icon = false;
			if (!empty($host->host_icon_image)) {
				$host_icon = html::image(Kohana::config('config.logos_path').$host->host_icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $host->host_icon_image_alt, 'title' => $host->host_icon_image_alt));
			}
		?>
		<tr class="<?php echo ($i % 2 == 0) ? 'odd' : 'even' ?>">
			<td class="icon bl <?php if (Command_Controller::_is_authorized_for_command(array('host_name' => $host->host_name)) === true) { ?>obj_properties <?php } echo strtolower(Current_status_Model::status_text($host->host_state, $host->host_has_been_checked, 'host')); ?>" id="<?php echo 'host|'.$host->host_name ?>"><em><?php echo Current_status_Model::status_text($host->host_state, $host->host_has_been_checked, 'host');?></em></td>
			<td class="item_select"><input type="checkbox" name="object_select[]" value="<?php echo $host->host_name ?>" class="checkbox_group_<?php echo $j; ?>" /></td>
			<td style="width: 180px"><a href="<?php echo url::base(true).'status/service?name='.urlencode($host->host_name).'&amp;hoststatustypes='.$this->hoststatustypes.'&amp;servicestatustypes='.(int)$servicestatustypes ?>" title="<?php echo $host->host_address ?>"><?php echo html::specialchars($host->host_name) ?></a></td>
			<td class="icon"><?php echo !empty($host_icon) ? $host_icon : '' ?></td>
			<td><?php
				if ($host->services_ok) {
					echo '<img src="'.ninja::add_path('icons/12x12/shield-ok.png').'" alt="" title="'._('OK').'" class="status-default" />';
					echo '<a href="'.url::base(true).'status/service?name='.urlencode($host->host_name).'&amp;servicestatustypes='.nagstat::SERVICE_OK.'&amp;hoststatustypes='.$this->hoststatustypes.'&amp;hostproperties='.$this->hostprops.'&amp;serviceprops='.$this->serviceprops.'" class="status-ok">'.$host->services_ok.' '._('OK').'</a> &nbsp; ';
				}
				if ($host->services_warning) {
					echo '<img src="'.ninja::add_path('icons/12x12/shield-warning.png').'" alt="" title="'._('Warning').'" class="status-default" />';
					echo '<a href="'.url::base(true).'status/service?name='.urlencode($host->host_name).'&amp;servicestatustypes='.nagstat::SERVICE_WARNING.'&amp;hoststatustypes='.$this->hoststatustypes.'&amp;hostproperties='.$this->hostprops.'&amp;serviceprops='.$this->serviceprops.'" class="status-warning">'.$host->services_warning.' '._('Warning').'</a> &nbsp; ';
				}
				if ($host->services_critical) {
					echo '<img src="'.ninja::add_path('icons/12x12/shield-critical.png').'" alt="" title="'._('Critical').'" class="status-default" />';
					echo '<a href="'.url::base(true).'status/service?name='.urlencode($host->host_name).'&amp;servicestatustypes='.nagstat::SERVICE_CRITICAL.'&amp;hoststatustypes='.$this->hoststatustypes.'&amp;hostproperties='.$this->hostprops.'&amp;serviceprops='.$this->serviceprops.'" class="status-critical">'.$host->services_critical.' '._('Critical').'</a> &nbsp; ';
				}
				if ($host->services_unknown) {
					echo '<img src="'.ninja::add_path('icons/12x12/shield-unknown.png').'" alt="" title="'._('Unknown').'" class="status-default" />';
					echo '<a href="'.url::base(true).'status/service?name='.urlencode($host->host_name).'&amp;servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&amp;hoststatustypes='.$this->hoststatustypes.'&amp;hostproperties='.$this->hostprops.'&amp;serviceprops='.$this->serviceprops.'" class="status-unknown">'.$host->services_unknown.' '._('Unknown').'</a> &nbsp; ';
				}
				if ($host->services_pending) {
					echo '<img src="'.ninja::add_path('icons/12x12/shield-pending.png').'" alt="" title="'._('Pending').'" class="status-default" />';
					echo '<a href="'.url::base(true).'status/service?name='.urlencode($host->host_name).'&amp;servicestatustypes='.nagstat::SERVICE_PENDING.'&amp;hoststatustypes='.$this->hoststatustypes.'&amp;hostproperties='.$this->hostprops.'&amp;serviceprops='.$this->serviceprops.'" class="status-pending">'.$host->services_pending.' '._('Pending').'</a> &nbsp; ';
				} ?>
			</td>
			<td style="text-align: left; width: 133px">
				<?php
				if ($nacoma_link===true) {
					$lable_nacoma = _('Configure this host using NACOMA (Nagios Configuration Manager)');
					echo '<a href="'.url::base(true).'configuration/configure?type=host&amp;name='.urlencode($host->host_name).'" style="border: 0px"><img src="'.ninja::add_path('icons/16x16/nacoma.png').'" alt="'.$lable_nacoma.'" title="'.$lable_nacoma.'" /></a> ';
				}

				if ($host->host_pnpgraph_present) {
					$label_perf = _('Show performance graph');
					echo '<a href="'.url::base(true) . 'pnp?host='.urlencode($host->host_name).'&amp;srv=_HOST_" style="border: 0px"><img src="'.ninja::add_path('icons/16x16/pnp.png').'" alt="'.$label_perf.'" title="'.$label_perf.'" class="pnp_graph_icon" /></a> ';
				}

				$lable_extinfo_host = _('View Extended Information For This Host');
				echo '<a href="'.url::base(true).'extinfo/details?type=host&amp;host='.urlencode($host->host_name).'" style="border: 0px"><img src="'.ninja::add_path('icons/16x16/extended-information.gif').'" alt="'.$lable_extinfo_host.'" title="'.$lable_extinfo_host.'" /></a> ';

				if ( Kohana::config('nagvis.nagvis_path') ) {
					$lable_statusmap = _('Locate Host On Map');
					echo '<a href="'.url::base(true).'statusmap/host/'.urlencode($host->host_name).'" style="border: 0px"><img src="'.ninja::add_path('icons/16x16/locate-host-on-map.png').'" alt="'.$lable_statusmap.'" title="'.$lable_statusmap.'" /></a> ';
				}

				$lable_svc_status = _('View Service Details For This Host');
				echo '<a href="'.url::base(true).'status/service?name='.urlencode($host->host_name).'" style="border: 0px"><img src="'.ninja::add_path('icons/16x16/service-details.gif').'" alt="'.$lable_svc_status.'" title="'.$lable_svc_status.'" /></a> ';

				if (!is_null($host->host_action_url)) {
					$lable_host_action = _('Perform Extra Host Actions');
					echo '<a href="'.nagstat::process_macros($host->host_action_url, $host).'" style="border: 0px" target="'.$action_url_target.'"><img src="'.ninja::add_path('icons/16x16/host-actions.png').'" alt="'.$lable_host_action.'" title="'.$lable_host_action.'" /></a> ';
				}

				if (!is_null($host->host_notes_url)) {
					$lable_host_notes = _('View Extra Host Notes');
					echo '<a href="'.nagstat::process_macros($host->host_notes_url, $host).'" style="border: 0px" target="'.$notes_url_target.'"><img src="'.ninja::add_path('icons/16x16/host-notes.png').'" alt="'.$lable_host_notes.'" title="'.$lable_host_notes.'" /></a> ';
				} ?>
			</td>
		</tr>
		<?php } ?>
	</table>
<?php echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select', 'id' => 'multi_action_select'),
		array(
			'' => _('Select action'),
			'SCHEDULE_HOST_DOWNTIME' => _('Schedule downtime'),
			'ACKNOWLEDGE_HOST_PROBLEM' => _('Acknowledge'),
			'REMOVE_HOST_ACKNOWLEDGEMENT' => _('Remove problem acknowledgement'),
			'DISABLE_HOST_NOTIFICATIONS' => _('Disable host notifications'),
			'ENABLE_HOST_NOTIFICATIONS' => _('Enable host notifications'),
			'DISABLE_HOST_SVC_NOTIFICATIONS' => _('Disable notifications for all services'),
			'DISABLE_HOST_CHECK' => _('Disable active checks'),
			'ENABLE_HOST_CHECK' => _('Enable active checks')
			)
		); ?>
	<?php echo form::submit(array('id' => 'multi_object_submit', 'class' => 'item_select', 'value' => _('Submit'))); ?>
	<?php echo form::hidden('obj_type', 'host'); ?>
	</form>
	<br /><span id="multi_object_submit_progress" class="item_select"></span>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>
