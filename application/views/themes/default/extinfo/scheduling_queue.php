<div>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<h2><?php echo (isset($label_title)) ? $label_title : _('Scheduling queue'); ?></h2>
	<?php if(!$data) { ?>
	<p><?php echo _('Nothing scheduled'); ?></p>
	<?php if($host_search || $service_search) { ?>
		<p><?php echo _('You filtered services/hosts by their names.')." "._("Do you want to"); ?> <a href="<?php echo Kohana::config('config.site_domain') . 'index.php/'.Router::$controller.'/'.Router::$method ?>"><?php echo _("reset the search filter?"); ?></a></p>
		<?php
		}
		// abort early; returning from this scope will bubble up to post-render,
		// in contrary to exit()
		return;
	}
	echo form::open('extinfo/scheduling_queue', array('method' => 'get')); ?>
	<p><?php echo _('Search for') ?> <label><?php echo _('Host') ?>: <input name='host' value='<?php echo $host_search ?>' /></label> <label><?php echo _('Service') ?>: <input name='service' value='<?php echo $service_search ?>' /></label> <input type="submit" value="<?php echo _('Search') ?>" /></p>
	</form>
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
							echo html::anchor('command/submit?cmd_typ=DISABLE_SVC_CHECK&host='.urlencode($host).'&service='.urlencode($row->description), html::image($this->add_path('icons/16x16/disable-active-checks.png'), array('alt' => _('Disable active checks of this service'), 'title' => _('Disable active checks of this service'))),array('style' => 'border: 0px')).'&nbsp; ';
						else
							echo html::anchor('command/submit?cmd_typ=ENABLE_SVC_CHECK&host='.urlencode($host).'&service='.urlencode($row->description), html::image($this->add_path('icons/16x16/enable.png'), array('alt' => _('Enable active checks of this service'), 'title' => _('Enable active checks of this service'))),array('style' => 'border: 0px')).'&nbsp; ';

						echo html::anchor('command/submit?cmd_typ=SCHEDULE_SVC_CHECK&host='.urlencode($host).'&service='.urlencode($row->description), html::image($this->add_path('icons/16x16/re-schedule.png'), array('alt' => _('Re-schedule this service check'), 'title' => _('Re-schedule this service check'))),array('style' => 'border: 0px'));
					} else {
						if ($row->active_checks_enabled == true)
							echo html::anchor('command/submit?cmd_typ=DISABLE_HOST_CHECK&host='.urlencode($host),html::image($this->add_path('icons/16x16/disable-active-checks.png'), array('alt' => _('Disable active checks of this host'), 'title' => _('Disable active checks of this host'))),array('style' => 'border: 0px')).'&nbsp; ';
						else
							echo html::anchor('command/submit?cmd_typ=ENABLE_HOST_CHECK&host='.urlencode($host),html::image($this->add_path('icons/16x16/enable.png'), array('alt' => _('Enable active checks of this host'), 'title' => _('Enable active checks of this host'))),array('style' => 'border: 0px')).'&nbsp; ';

						echo html::anchor('command/submit?cmd_typ=SCHEDULE_HOST_CHECK&host='.urlencode($host),html::image($this->add_path('icons/16x16/re-schedule.png'), array('alt' => _('Re-schedule this host check'), 'title' => _('Re-schedule this host check'))),array('style' => 'border: 0px'));
					}
				?>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
