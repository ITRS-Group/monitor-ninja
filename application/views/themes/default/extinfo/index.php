<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');
$date_format_str = nagstat::date_format();

/* @var $set ObjectSet_Model */
$it = $set->it(false, array(), 1, 0);
/* @var $object Object_Model */
$object = $it->current();

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


?>
<div id="page_links">
	<em class="page-links-label"><?php echo _('View').', '.$label_view_for.':'; ?></em>
	<ul>
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
	<div class="clear"></div>
	<hr />
</div>

<?php
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
				echo '<td>'.$host->get_display_name();
				echo $host->get_alias() ? ' ('.$host->get_alias().')' : '';
				$host_link = html::anchor('extinfo/details/?host='.urlencode($host->get_name()), html::specialchars($host->get_name()));
				echo !empty($host_link) ? ' ('.$host_link.')' : '';
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
			<td><strong><?php echo _('Notifies to') ?></strong></td>
			<td>
				<?php	if ($object->get_contact_groups()) {
					$c = 0;
					foreach ($object->get_contact_groups() as $group) {
						echo '<a title="'._('Contactgroup').': '.$group.', '._('Click to view contacts').'" class="extinfo_contactgroup" id="extinfo_contactgroup_'.(++$c).'">';
						echo $group.'</a>';
/*				?>
				<table id="extinfo_contacts_<?php echo $c ?>" style="display:none;width:75%" class="extinfo_contacts">
					<tr>
						<th style="border: 1px solid #cdcdcd"><?php echo _('Contact name') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Alias') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Email') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Pager') ?></th>
					</tr>
					<?php
					foreach ($members as $member) { ?>
					<tr class="<?php echo ($c%2 == 0) ? 'even' : 'odd' ?>">
						<td><?php echo $member['name'] ?></td>
						<td><?php echo $member['alias'] ?></td>
						<td><?php echo $member['email'] ?></td>
						<td><?php echo $member['pager'] ?></td>
					</tr>
					<?php	} ?>
				</table>
					<?php*/
					}
				} else {
					echo _('No contactgroup');
				}
			?>
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
					if ($url =$object->get_action_url()) {
						echo '<a href="'.$url.'" style="border: 0px" target="'.$action_url_target.'">';
						echo html::image($this->add_path('icons/16x16/host-actions.png'),array('alt' => _('Perform extra '.$type.' actions'),'title' => _('Perform extra '.$type.' actions'),'style' => 'margin: 1px 5px 0px 0px')).'</a>';
						echo '<a href="'.$url.'" target="'.$action_url_target.'">'._('Extra actions').'</a>';
					}
					if ($url = $object->get_notes_url()) {
						echo '&nbsp; <a target="'.$notes_url_target.'" href="'.$url.'" style="border: 0px">';
						echo html::image($this->add_path('icons/16x16/host-notes.png'),array('alt' => _('View extra '.$type.' notes'),'title' => _('View extra '.$type.' notes'),'style' => 'margin: 1px 5px 0px 0px')).'</a>';
						echo '<a target="'.$notes_url_target.'" href="'.$url.'">'._('Extra notes').'</a>';
					}
					if ($url = $object->get_config_url()) {
						echo '&nbsp; <a href="'.$url.'" style="border: 0px">';
						echo html::image($this->add_path('icons/16x16/nacoma.png'),array('alt' => _('View extra '.$type.' notes'),'title' => _('View extra '.$type.' notes'),'style' => 'margin: 1px 5px 0px 0px')).'</a>';
						echo '<a href="'.$url.'">'._('Configure').'</a>';
					}
					if ($object->get_pnpgraph_present()) {
						$url = url::site() . 'pnp/?host=' . urlencode($host->get_name());
						if($service!==false) {
							$url .= '&srv=' . urlencode($service->get_description());
						} else {
							$url .= '&srv=_HOST_';
						}
						echo '&nbsp; <a href="'.$url.'" style="border: 0px">';
						echo html::image($this->add_path('icons/16x16/pnp.png'),array('alt' => _('Show performance graph'),'title' => _('Show performance graph'),'style' => 'margin: 1px 5px 0px 0px')).'</a>';
						echo '<a href="'.$url.'">'._('Show performance graph').'</a>';
					}
				?><div id="pnp_area" style="display:none"></div>
			</td>
		</tr>
	</table>
</div>

<?php /* $this->session->set('back_extinfo',$back_link); */ ?>


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
			<td class="bt">
				<span class="status-<?php echo strtolower($object->get_state_text()) ?>"><span class="icon-12 x12-shield-<?php echo strtolower($object->get_state_text()); ?>"></span><?php echo ucfirst(strtolower($object->get_state_text())) ?></span>
				(<?php echo _('for'); ?> <?php echo $object->get_duration()>=0 ? time::to_string($object->get_duration()) : _('N/A') ?>)
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Status information'); ?></td>
			<td style="white-space: normal"><?php echo $object->get_plugin_output() ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Performance data'); ?></td>
			<td style="white-space: normal"><?php echo security::xss_clean($object->get_perf_data()) ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Current attempt'); ?></td>
			<td><?php echo $object->get_current_attempt() ?>/<?php echo $object->get_max_check_attempts() ?> (<?php echo strtolower($object->get_state_type_text_uc()) ?>)</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Last check time'); ?></td>
			<td><?php echo $object->get_last_check() ? date($date_format_str, $object->get_last_check()) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Check type'); ?></td>
			<td>
				<span class="<?php echo $object->get_check_type_str() ?>"><?php echo ucfirst($object->get_check_type_str()) ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Check latency / duration'); ?></td>
			<td><?php echo $object->get_latency() ?> / <?php echo number_format($object->get_execution_time(), 3) ?> <?php echo _('seconds'); ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $service!==false?_('Next scheduled active check'):_('Next scheduled check') ?></td>
			<td><?php echo $object->get_next_check() && $object->get_active_checks_enabled() ? date($date_format_str, $object->get_next_check()) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Last state change'); ?></td>
			<td><?php echo $object->get_last_state_change() ? date($date_format_str, $object->get_last_state_change()) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Last notification'); ?></td>
			<?php $last_notification = $object->get_last_notification()!=0 ? date(nagstat::date_format(), $object->get_last_notification()) : _('N/A'); ?>
			<td><?php echo $last_notification ?>&nbsp;(<?php echo _('Notifications'); ?>: <?php echo $object->get_current_notification_number() ?>)</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Is this '.$type.' flapping?') ?></td>
			<td>
			<?php
			$flap_value = $object->get_flap_detection_enabled() && $object->get_is_flapping() ? _('YES') : _('NO');
			$percent_state_change_str = '('.number_format((int)$object->get_percent_state_change(), 2).'% '._('state change').')';
			?>
				<span class="flap-<?php echo strtolower($flap_value); ?>"><?php echo ucfirst(strtolower($flap_value)).'</span> '.$percent_state_change_str; ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('In scheduled downtime?'); ?></td>
			<td>
				<span class="downtime-<?php echo strtolower($object->get_scheduled_downtime_depth()); ?>"><?php echo $object->get_scheduled_downtime_depth() ? _('Enabled') : _('Disabled'); ?></span>
			</td>
		</tr>
		<tr>
			<td  class="dark" style="width: 160px"><?php echo _('Active checks'); ?></td>
			<td>
				<span class="<?php echo $object->get_active_checks_enabled() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_active_checks_enabled() ? _('Enabled') : _('Disabled'); ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Passive checks'); ?></td>
			<td>
				<span class="<?php echo $object->get_accept_passive_checks() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_accept_passive_checks() ? _('Enabled') : _('Disabled'); ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Obsessing'); ?></td>
			<td>
				<span class="<?php echo $object->get_obsess() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_obsess() ? _('Enabled') : _('Disabled'); ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Notifications'); ?></td>
			<td>
				<span class="<?php echo $object->get_notifications_enabled() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_notifications_enabled() ? _('Enabled') : _('Disabled'); ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo _('Event handler'); ?></td>
			<td>
				<span class="<?php echo $object->get_event_handler_enabled() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_event_handler_enabled() ? _('Enabled') : _('Disabled'); ?></span>
			</td>
		</tr>
		<tr>

			<td class="dark"><?php echo _('Flap detection') ?></td>
			<td>
				<span class="<?php echo $object->get_flap_detection_enabled() ? _('enabled') : _('disabled'); ?>"><?php echo $object->get_flap_detection_enabled() ? _('Enabled') : _('Disabled'); ?></span>
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
if (isset($comments))
	echo $comments;
