<div class="widget w98 left">
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<h2><?php echo (isset($label_title)) ? $label_title : _('Scheduling queue'); ?></h2>
	<table cellpadding="2" cellspacing="0" class="schedule_search" border=1>
		<tr>
			<form action="">
			<td><?php echo _('Filter').': '; ?>
			<td colspan="2"><?php echo form::input(array('id' => 'hostfilterbox', 'style' => 'color:grey', 'class' => 'filterboxfield'), $filter_string).' '.form::button('clearhostsearch', _('Clear')); ?></td>
			</form>
		</tr>
		<tr>
			<?php echo form::open('extinfo/scheduling_queue', array('method' => 'get')); ?>
			<td><?php echo _('Search Host').': '; ?></td>
			<td><?php echo form::input(array('id' => 'hostsearch', 'name' => 'host_name')).' '._('Service').': '.form::input(array('id' => 'svcsearch', 'name' => 'service')).' '.form::button('submitsearch', _('Search'));; ?></td>
			<td><?php echo $search_active ? form::button(array('id' => 'reload_page', 'name' => 'reload_page'), _('Clear Search')) : '&nbsp;'; ?></td>
			<?php echo form::close(); ?>
		</tr>
	</table>
	<table id="hostcomments_table">
		<tr>
			<?php
				$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
				$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'next_check';
				foreach($header_links as $row) {
					if (isset($row['url_desc'])) {
						echo '<th class="header'.
						(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' :
						(($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' :
						(isset($row['url_desc']) ? '' : 'None'))) . '"';
						if (isset($row['url_desc'])) // assumption: url_desc iff url_asc
							echo ' onclick="location.href=\'' . Kohana::config('config.site_domain') . 'index.php/'.($order != 'DESC' ? $row['url_desc'] : $row['url_asc']).'\'"';
						echo '>';
						echo $row['title'];
						echo '</th>';
					}
				}
			?>
			<th class="headerNone"><?php echo _('Type'); ?></th>
			<th class="headerNone"><?php echo _('Active checks'); ?></th>
			<th class="headerNone"><?php echo _('Actions'); ?></th>
		</tr>
		<?php
			$i = 0;
			if ($data!==false && count($data)) {
				foreach ($data as $row) {
					$i++;
		?>
		<tr class="<?php echo $i%2 == 0 ? 'odd' : 'even'; ?>">
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name); ?></td>
			<td style="white-space: normal"><?php if ($row->service_description) {echo html::anchor('extinfo/details/service/'.$row->host_name.'/?service='.$row->service_description, $row->service_description);} ?>&nbsp;</td>
			<td><?php echo date($date_format_str,$row->last_check); ?></td>
			<td><?php echo date($date_format_str,$row->next_check); ?></td>
			<td>
				<?php
					if($row->check_type == nagstat::CHECK_OPTION_NONE)
						echo _('Normal');
					else{
						if($row->check_type == nagstat::CHECK_OPTION_FORCE_EXECUTION)
							echo _('Forced');
						if($row->check_type == nagstat::CHECK_OPTION_FRESHNESS_CHECK)
							echo _('Freshness');
						if($row->check_type == nagstat::CHECK_OPTION_ORPHAN_CHECK)
							echo _('Orphan');
					}
				?>
			</td>
			<td><span class="<?php echo ($row->active_checks_enabled == true ? 'enabled' : 'disabled');?>"><?php	echo $row->active_checks_enabled == true ? _('ENABLED') : _('DISABLED');?></span></td>
			<td class="icon">
				<?php
					if ($row->active_checks_enabled == true)
						echo html::anchor('command/submit?cmd_typ=DISABLE_HOST_CHECK&host='.urlencode($row->host_name),html::image($this->add_path('icons/16x16/disable-active-checks.png'), array('alt' => _('Disable active checks of this host'), 'title' => _('Disable active checks of this host'))),array('style' => 'border: 0px')).'&nbsp; ';
					else
						echo html::anchor('command/submit?cmd_typ=ENABLE_HOST_CHECK&host='.urlencode($row->host_name),html::image($this->add_path('icons/16x16/enable.png'), array('alt' => _('Enable active checks of this host'), 'title' => _('Enable active checks of this host'))),array('style' => 'border: 0px')).'&nbsp; ';

					echo html::anchor('command/submit?cmd_typ=SCHEDULE_HOST_CHECK&host='.urlencode($row->host_name),html::image($this->add_path('icons/16x16/re-schedule.png'), array('alt' => _('Re-schedule this host check'), 'title' => _('Re-schedule this host check'))),array('style' => 'border: 0px'));
				?>
			</td>
		</tr>
		<?php } } else { ?>
		<tr class="even">
			<td colspan="7"><?php echo _('Nothing scheduled'); ?></td>
		</tr>
		<?php } ?>
	</table>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>

<?php $this->session->set('back_extinfo',$back_link);?>
