<div>
	<?php if(!$data) { ?>
	<p><?php echo _('Nothing scheduled'); ?></p>
	<?php
		// abort early; returning from this scope will bubble up to post-render,
		// in contrary to exit()
		return;
	} ?>
	<table id="hostcomments_table">
		<tr>
			<?php foreach($header_links as $column => $title) { ?>
					<th>
						<?php echo $title; ?>
					</th>
			<?php } ?>
			<th><?php echo _('Type'); ?></th>
			<th><?php echo _('Active checks'); ?></th>
			<th><?php echo _('Actions'); ?></th>
		</tr>
		<?php
			$check_types = array(
				nagstat::CHECK_OPTION_NONE => _('Normal'),
				nagstat::CHECK_OPTION_FORCE_EXECUTION => _('Forced'),
				nagstat::CHECK_OPTION_FRESHNESS_CHECK => _('Freshness'),
				nagstat::CHECK_OPTION_ORPHAN_CHECK => _('Orphan')
			);

			/**
			 * @return object $row | false
			 */
			$total_rows_printed = -1;
			foreach( $data as $row ) {
				$total_rows_printed++;
				$host = isset($row->host_name) ? $row->host_name : $row->name;
		?>
		<tr class="<?php echo $total_rows_printed%2 == 0 ? 'odd' : 'even'; ?>">
			<td><a href="<?php echo url::base(true); ?>/extinfo/details/host/<?php echo $host ?>"><?php echo $host ?></a></td>
			<td style="white-space: normal"><?php if(isset($row->description)) {echo html::anchor('extinfo/details/service/'.$row->host_name.'/?service='.$row->description, $row->description);} ?>&nbsp;</td>
			<td><?php echo $row->last_check ? date($date_format_str,$row->last_check) : _('Never checked'); ?></td>
			<td><?php echo $row->next_check ? date($date_format_str,$row->next_check) : _('No check scheduled'); ?></td>
			<td>
				<?php
					$types = array();
					foreach($check_types as $option => $text) {
						if(($row->check_type == 0 && $option == 0) || $row->check_type & $option) {
							$types[] = $text;
						}
					}
					echo implode(", ", $types);
				?>
			</td>
			<td><span class="<?php echo ($row->active_checks_enabled ? 'enabled' : 'disabled');?>"><?php echo $row->active_checks_enabled ? _('ENABLED') : _('DISABLED');?></span></td>
			<td class="icon">
				<?php
					if(isset($row->description)) {
						if ($row->active_checks_enabled == true)
							echo html::anchor('command/submit?cmd_typ=DISABLE_SVC_CHECK&host='.urlencode($host).'&service='.urlencode($row->description), '<span class="icon-16 x16-disable-active-checks" title="Disable active checks of this service"></span>' , array('style' => 'border: 0px')).'&nbsp; ';
						else
							echo html::anchor('command/submit?cmd_typ=ENABLE_SVC_CHECK&host='.urlencode($host).'&service='.urlencode($row->description), '<span class="icon-16 x16-disable-active-checks" title="Enable active checks of this service"></span>',array('style' => 'border: 0px')).'&nbsp; ';

						echo html::anchor('command/submit?cmd_typ=SCHEDULE_SVC_CHECK&host='.urlencode($host).'&service='.urlencode($row->description), '<span class="icon-16 x16-re-schedule" title="Re-schedule this service check"></span>',array('style' => 'border: 0px'));
					} else {
						if ($row->active_checks_enabled == true)
							echo html::anchor('command/submit?cmd_typ=DISABLE_HOST_CHECK&host='.urlencode($host),'<span class="icon-16 x16-disable-active-checks" title="Disable active checks of this host"></span>',array('style' => 'border: 0px')).'&nbsp; ';
						else
							echo html::anchor('command/submit?cmd_typ=ENABLE_HOST_CHECK&host='.urlencode($host),'<span class="icon-16 x16-disable-active-checks" title="Enable active checks of this host"></span>',array('style' => 'border: 0px')).'&nbsp; ';

						echo html::anchor('command/submit?cmd_typ=SCHEDULE_HOST_CHECK&host='.urlencode($host),'<span class="icon-16 x16-re-schedule" title="Re-schedule this host check"></span>',array('style' => 'border: 0px'));
					}
				?>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
