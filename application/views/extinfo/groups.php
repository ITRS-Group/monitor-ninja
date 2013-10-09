<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');
?>
<div id="page_links">
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
</div>
<div class="clear"> </div>

<?php if (!empty($action_url)) { ?>
<a href="<?php echo $action_url ?>" style="border: 0px" target="<?php echo $action_url_target ?>">
			<?php echo html::image($this->add_path('icons/16x16/host-actions.png'),array('alt' => _('Perform extra host actions'),'title' => _('Perform extra host actions')))?></a>
<br />
<strong><?php echo _('Extra actions') ?></strong>
<?php } ?>
<br />

<?php if (!empty($notes_url)) { ?>
<a href="<?php echo $notes_url ?>" style="border: 0px" target="<?php echo $notes_url_target ?>">
			<?php echo html::image($this->add_path('icons/16x16/host-notes.png'),array('alt' => _('View extra host notes'),'title' => _('View extra host notes')))?></a>
<br />
<strong><?php echo _('Extra notes') ?></strong>
<?php }

if (!empty($notes)) {?>
	<br /><strong><?php echo _('Notes') ?></strong>: <?php echo $notes;
}


$group_attribute = strtolower($grouptype) . '_name';

?>

<div>
<table class="ext">
	<caption><?php echo ucfirst($label_grouptype) ?> <?php echo _('Commands').' '._('for').': '.security::xss_clean($group_alias).' ('.$groupname.')'; ?>
		<?php if (nacoma::link()===true)
			echo nacoma::link('configuration/configure/'.$grouptype.'/'.urlencode($groupname), 'icons/16x16/nacoma.png', sprintf(_('Configure this %sgroup'), $grouptype));?>
	</caption>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/scheduled-downtime.png'), array('alt' => _('Schedule downtime for all hosts in this').' '.$label_grouptype, 'title' => _('Schedule downtime for all hosts in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_schedule_downtime_hosts.'&'.$group_attribute.'='.$groupname, _('Schedule downtime for all hosts in this')." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/scheduled-downtime.png'), array('alt' => _('Schedule downtime for all services in this').' '.$label_grouptype, 'title' => _('Schedule downtime for all services in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_schedule_downtime_services.'&'.$group_attribute.'='.$groupname, _('Schedule downtime for all services in this')." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/notify.png'), array('alt' => _('Enable').' '._('notifications for all hosts in this').' '.$label_grouptype, 'title' => _('Enable').' '._('notifications for all hosts in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_notifications_hosts.'&'.$group_attribute.'='.$groupname, _('Enable')." "._('notifications for all hosts in this')." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/notify-disabled.png'), array('alt' => _('Disable').' '._('notifications for all hosts in this').' '.$label_grouptype, 'title' => _('Disable').' '._('notifications for all hosts in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_notifications_hosts.'&'.$group_attribute.'='.$groupname, _('Disable')." "._('notifications for all hosts in this')." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/notify.png'), array('alt' => _('Enable').' '._('notifications for all services in this').' '.$label_grouptype, 'title' => _('Enable').' '._('notifications for all services in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_notifications_services.'&'.$group_attribute.'='.$groupname, _('Enable')." "._('notifications for all services in this')." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/notify-disabled.png'), array('alt' => _('Disable').' '._('notifications for all services in this').' '.$label_grouptype, 'title' => _('Disable').' '._('notifications for all services in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_notifications_services.'&'.$group_attribute.'='.$groupname, _('Disable').' '._('notifications for all services in this')." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/enabled.png'), array('alt' => _('Enable').' '._('active checks of all hosts in this').' '.$label_grouptype, 'title' => _('Enable').' '._('active checks of all hosts in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_active_host_checks.'&'.$group_attribute.'='.$groupname, _('Enable')." "._('active checks of all hosts in this')." ".$label_grouptype); ?>
	</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => _('Disable').' '._('active checks of all hosts in this').' '.$label_grouptype, 'title' => _('Disable').' '._('active checks of all hosts in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_active_host_checks.'&'.$group_attribute.'='.$groupname, _('Disable')." "._('active checks of all hosts in this')." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/enabled.png'), array('alt' => _('Enable').' '._('active checks of all services in this').' '.$label_grouptype, 'title' => _('Enable').' '._('active checks of all services in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_active_svc_checks.'&'.$group_attribute.'='.$groupname, _('Enable')." "._('active checks of all services in this')." ".$label_grouptype); ?>
	</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => _('Disable').' '._('active checks of all services in this').' '.$label_grouptype, 'title' => _('Disable').' '._('active checks of all services in this').' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_active_svc_checks.'&'.$group_attribute.'='.$groupname, _('Disable')." "._('active checks of all services in this')." ".$label_grouptype); ?>
		</td>
	</tr>
</table>
</div>
