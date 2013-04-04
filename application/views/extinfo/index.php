<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');
$date_format_str = nagstat::date_format();

$green_shield = '<span class="icon-12 x12-shield-up"></span>';
$red_shield = '<span class="icon-12 x12-shield-down"></span>';
$enabled =  $green_shield.' '._('Enabled');
$disabled = $red_shield.' '._('Disabled');
$yes = _('Yes');
$no = _('No');

if( $object instanceof Host_Model ) {
	$host = $object;
	$service = false;
	$type = 'host';
} else if( $object instanceof Service_Model ) {
	$host = $object->get_host();
	$service = $object;
	$type = 'service';
}
/* @var $host Host_Model */
/* @var $service Service_Model */
/* @var $object Service_Model */


if (isset($page_links)) {
?>
<div class="padding_plz">
<div id="page_links">
	<em class="page-links-label"><?php echo _('View').', '.$label_view_for.':'; ?></em>
	<ul>
	<?php foreach ($page_links as $label => $link) { ?>
		<li><?php echo html::anchor($link, $label) ?></li>
	<?php } ?>
	</ul>
	<div class="clear"></div>
</div>
<?php
}
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div id="extinfo_host-info">
	<table>
		<tr>
			<th colspan="2" style="padding: 5px 0px" >
				<?php echo $object->get_icon_image() ? html::image(Kohana::config('config.logos_path').$object->get_icon_image(), array('alt' => $object->get_icon_image_alt(), 'title' => $object->get_icon_image_alt(), 'style' => 'width: 32px; margin: -5px 7px 0px 0px; float: left')) : ''?>
				<h1 style="display: inline"><?php echo ($type=='host' ? $object->get_alias().' ('.$object->get_display_name().')' : $object->get_display_name()) ?></h1>
			</th>
		</tr>
		<?php
			if ($service !== false) {
				echo '<tr>';
				echo '<td style="width: 80px"><strong>'._('On host').'</strong></td>';
				echo '<td>';
				echo html::anchor('extinfo/details/?host='.urlencode($host->get_name()), html::specialchars($host->get_display_name()));
				echo $host->get_alias() ? ' ('.$host->get_alias().')' : '';
				echo '</td>';
				echo '</tr>';
			}
		?>
		<tr>
			<td style="width: 80px"><strong><?php echo _('Address');?></strong></td>
			<td><?php echo $host->get_address(); ?></td>
		</tr>
		<?php if ($host->get_parents()) { ?>
		<tr>
			<td><strong><?php echo _('Parents') ?></strong></td>
			<td>
				<?php
					echo implode(
						',',
						array_map(
							function($parent) {
								return html::anchor('status/service/'.$parent, $parent);
							},
							$host->get_parents()
						)
					);
				?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td><strong><?php echo _('Member of'); ?></strong></td>
			<td style="white-space: normal"><?php echo $object->get_groups() ? implode(', ', $object->get_groups()) : _('No '.$type.'groups') ?></td>
		</tr>
		<tr>
			<td><strong><?php echo _('Contact groups') ?></strong></td>
			<td>
				<?php 
					$c = 0;
					$groups = $object->get_contact_groups();
					$contacts = $object->get_contacts();
					
					foreach ($groups as $group) {
						echo '<a href="#" title="'._('Contactgroup').': '.$group.', '._('Click to view contacts').'" class="extinfo_contactgroup" id="extinfo_contactgroup_'.(++$c).'">';
						echo $group.'</a>';
				?>
				<table id="extinfo_contacts_<?php echo $c ?>" style="display:none;width:75%" class="extinfo_contacts">
					<tr>
						<th style="border: 1px solid #cdcdcd"><?php echo _('Contact name') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Alias') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Email') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Pager') ?></th>
					</tr>
					<?php
					$gobj = ContactGroupPool_Model::all()->reduce_by('name',$group,'=')->one(array('members'));
					
					$gmembers = ContactPool_Model::none();
					$allcontacts = ContactPool_Model::all();

					$group_member_names = $gobj->get_members();
					$contacts = array_diff( $contacts, $group_member_names );
					
					foreach( $group_member_names as $member ) {
						$gmembers = $gmembers->union($allcontacts->reduce_by('name', $member, '='));
					}
					
					foreach ($gmembers as $member) { ?>
					<tr class="<?php echo ($c%2 == 0) ? 'even' : 'odd' ?>">
						<td><?php echo $member->get_name(); ?></td>
						<td><?php echo $member->get_alias(); ?></td>
						<td><?php echo $member->get_email(); ?></td>
						<td><?php echo $member->get_pager(); ?></td>
					</tr>
					<?php } ?>
				</table>
				<?php } ?>
				<?php if( count( $contacts ) ) { ?>
			</td>
		</tr>
		<tr>
			<td><strong><?php echo _('Contacts') ?></strong></td>
			<td>
				<table class="extinfo_contacts" style="display: table;">
					<tr>
						<th style="border: 1px solid #cdcdcd"><?php echo _('Contact name') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Alias') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Email') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Pager') ?></th>
					</tr>
					<?php
					
					$gmembers = ContactPool_Model::none();
					$allcontacts = ContactPool_Model::all();
					foreach( $contacts as $member ) {
						$gmembers = $gmembers->union($allcontacts->reduce_by('name', $member, '='));
					}
					
					foreach ($gmembers as $member) { ?>
					<tr class="<?php echo ($c%2 == 0) ? 'even' : 'odd' ?>">
						<td><?php echo $member->get_name(); ?></td>
						<td><?php echo $member->get_alias(); ?></td>
						<td><?php echo $member->get_email(); ?></td>
						<td><?php echo $member->get_pager(); ?></td>
					</tr>
					<?php } ?>
				</table>
				<?php } ?>
			</td>
		</tr>
		<?php if ($object->get_notes()) {?>
		<tr>
			<td><strong><?php echo _('Notes') ?></strong></td>
			<td><?php echo $object->get_notes() ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="2" style="padding-top: 7px">
				<?php
					if ($url = $object->get_action_url()) {
						echo '<a href="'.$url.'" target="'.$action_url_target.'">';
						echo '<span class="icon-16 x16-host-actions" title="'. _('Perform extra '.$type.' actions').'"></span>';
						echo _('Extra actions').'</a>';
					}
					if ($url = $object->get_notes_url()) {
						echo '&nbsp; <a target="'.$notes_url_target.'" href="'.$url.'">';
						echo '<span class="icon-16 x16-host-notes" title="'. _('View extra '.$type.' notes').'"></span>';
						echo _('Extra notes').'</a>';
					}
					if ($url = $object->get_config_url()) {
						echo '&nbsp; <a href="'.$url.'">';
						echo '<span class="icon-16 x16-nacoma" title="'. _('Configure '.$type).'"></span>';
						echo _('Configure').'</a>';
					}
					if ($object->get_pnpgraph_present()) {
						$url = url::site() . 'pnp/?host=' . urlencode($host->get_name());
						$pnp_host = $host->get_name();
						$pnp_service = "_HOST_";
						if($service!==false) {
							$url .= '&srv=' . urlencode($service->get_description());
							$pnp_service = $service->get_description();
						} else {
							$url .= '&srv=_HOST_';
						}
						echo '&nbsp; <a class="pnp_graph" data-host="'.$pnp_host.'" data-srv="'.$pnp_service.'" href="'.$url.'">';
						echo '<span class="icon-16 x16-pnp"></span>';
						echo _('Show performance graph').'</a>';
					}
				?>
			</td>
		</tr>
	</table>
</div>

<div class="clear"></div>

<br /><br />
<div class="left width-50" id="extinfo_current">
	<?php
	if (!$object->get_has_been_checked()) {
		echo $object->get_key()."<br /><br />";
		echo _('This '.$type.' has not yet been checked, so status information is not available.');
	} else { ?>
	<table class="ext">
		<tr>
			<th colspan="2"><?php echo $object->get_key() ?></th>
		</tr>
		<tr>
			<td style="width: 160px" class="dark bt"><?php echo _('Current status'); ?></td>
			<td class="bt" id="field_current_status">
				<span class="status-<?php echo strtolower($object->get_state_text()) ?>"><span class="icon-12 x12-shield-<?php echo strtolower($object->get_state_text()); ?>"></span><?php echo ucfirst(strtolower($object->get_state_text())) ?></span>
				(<?php echo _('for'); ?> <?php echo $object->get_duration()>=0 ? time::to_string($object->get_duration()) : _('N/A') ?>)
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Status information'); ?></td>
			<td style="white-space: normal" id="field_plugin_output"><?php echo $object->get_plugin_output() ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Performance data'); ?></td>
			<td style="white-space: normal" id="field_perf_data"><?php echo security::xss_clean($object->get_perf_data()) ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Current attempt'); ?></td>
			<td id="field_current_attempt"><?php echo $object->get_current_attempt() ?>/<?php echo $object->get_max_check_attempts() ?> (<?php echo strtolower($object->get_state_type_text_uc()) ?>)</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Last check time'); ?></td>
			<td id="field_last_check"><?php echo $object->get_last_check() ? date($date_format_str, $object->get_last_check()) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Check type'); ?></td>
			<td id="field_check_type">
				<span class="<?php echo $object->get_check_type_str() ?>"><?php echo ($object->get_check_type_str() == 'active' ? $green_shield : $red_shield).' '.ucfirst($object->get_check_type_str()) ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Check latency / duration'); ?></td>
			<td id="field_latency"><?php echo $object->get_latency() ?> / <?php echo number_format($object->get_execution_time(), 3) ?> <?php echo _('seconds'); ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $service!==false?_('Next scheduled active check'):_('Next scheduled check') ?></td>
			<td id="field_next_check"><?php echo $object->get_next_check() && $object->get_active_checks_enabled() ? date($date_format_str, $object->get_next_check()) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Last state change'); ?></td>
			<td id="field_last_state_change"><?php echo $object->get_last_state_change() ? date($date_format_str, $object->get_last_state_change()) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Last notification'); ?></td>
			<?php $last_notification = $object->get_last_notification()!=0 ? date(nagstat::date_format(), $object->get_last_notification()) : _('N/A'); ?>
			<td id="field_last_notification"><?php echo $last_notification ?>&nbsp;(<?php echo _('Notifications'); ?>: <?php echo $object->get_current_notification_number() ?>)</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Is this '.$type.' flapping?') ?></td>
			<td id="field_flap_detection_enabled">
			<?php
			$flap_value = $object->get_flap_detection_enabled() && $object->get_is_flapping() ? $red_shield.' '.$yes : $green_shield.' '.$no;
			$percent_state_change_str = '('.number_format((int)$object->get_percent_state_change(), 2).'% '._('state change').')';
			echo $flap_value.' '.$percent_state_change_str; ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('In scheduled downtime?'); ?></td>
			<td id="field_scheduled_downtime">
				<?php echo $object->get_scheduled_downtime_depth() ? $red_shield.' '.$yes : $green_shield.' '.$no; ?>
			</td>
		</tr>
		<tr>
			<td  class="dark" style="width: 160px"><?php echo _('Active checks'); ?></td>
			<td id="field_active_checks_enabled">
				<span class="<?php echo $object->get_active_checks_enabled() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_active_checks_enabled() ? $enabled : $disabled; ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Passive checks'); ?></td>
			<td id="field_accept_passive_checks">
				<span class="<?php echo $object->get_accept_passive_checks() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_accept_passive_checks() ? $enabled : $disabled; ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Obsessing'); ?></td>
			<td id="field_obsess">
				<span class="<?php echo $object->get_obsess() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_obsess() ? $enabled : $disabled; ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Notifications'); ?></td>
			<td id="field_notifications">
				<span class="<?php echo $object->get_notifications_enabled() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_notifications_enabled() ? $enabled : $disabled; ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Event handler'); ?></td>
			<td id="field_event_handler">
				<span class="<?php echo $object->get_event_handler_enabled() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_event_handler_enabled() ? $enabled : $disabled; ?></span>
			</td>
		</tr>
		<tr>

			<td class="dark"><?php echo _('Flap detection') ?></td>
			<td id="field_flap_detection">
				<span class="<?php echo $object->get_flap_detection_enabled() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_flap_detection_enabled() ? $enabled : $disabled; ?></span>
			</td>
		</tr>
		<?php if($object->get_custom_variables()) {
			foreach($object->get_custom_variables() as $variable => $value) { 
				if (substr($variable, 0, 6) !== 'OP5H__') { ?>
				<tr>
					<td class="dark">_<?php echo $variable ?></td>
					<td><?php echo link::linkify($value) ?></td>
				</tr>
		<?php
				}
			}
		} ?>
			</table>
<?php } ?>
</div>

<?php
if (!empty($commands))
	echo $commands;
?>

<div class="clear"></div>
<br /><br />

<?php
if (isset($comments)) {

	$label = _("Submit a $type comment");
	$cmd = $type == 'host' ? nagioscmd::command_id('ADD_HOST_COMMENT') : nagioscmd::command_id('ADD_SVC_COMMENT');
	echo '<span class="icon-16 x16-add-comment" title="' . htmlentities($label) . '"></span>';
	echo nagioscmd::command_link($cmd, $host->get_name(), $service === false ? false : $service->get_description(), $label, 'submit', false, array('id'=>'submit_comment_button'));

	$label = _("Delete all $type comments");
	$cmd = $type == 'host' ? nagioscmd::command_id('DEL_ALL_HOST_COMMENTS') : nagioscmd::command_id('DEL_ALL_SVC_COMMENTS');
	echo '<span class="icon-16 x16-delete-comment" title="' . htmlentities($label) . '"></span>';
	echo nagioscmd::command_link($cmd, $host->get_name(), $service === false ? false : $service->get_description(), $label, 'submit', false, array('id'=>'delete_all_comments_button'));

	echo $comments;
}

if (isset($downtimes))
	echo $downtimes;
?>
</div>
