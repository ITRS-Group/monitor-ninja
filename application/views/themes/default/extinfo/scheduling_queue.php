<div class="widget w98 left">
	<?php //echo (isset($pagination)) ? $pagination : ''; ?>
	<table id="host_table">
		<caption><?php echo (isset($label_title)) ? $label_title : $this->translate->_('Scheduling queue'); ?></caption>
		<tr>
			<?php
				$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
				$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'next_check';
				foreach($header_links as $row) {
					if (isset($row['url_desc'])) {
						echo '<th class="header'.
						(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' :
						(($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' :
						(isset($row['url_desc']) ? '' : 'None'))) .
							'"onclick="location.href=\'' . Kohana::config('config.site_domain') . 'index.php/'.((isset($row['url_desc']) && $order == 'ASC') ? str_replace('&','&amp;',$row['url_desc']) : ((isset($row['url_asc']) && $order == 'DESC') ? str_replace('&','&amp;',$row['url_asc']) : '')).'\'">';
						echo $row['title'];
						echo '</th>';
					}
				}
			?>
			<th class="headerNone"><?php echo $this->translate->_('Type'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Active checks'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Actions'); ?></th>
		</tr>
		<?php
			$i = 0;
			if ($data!==false && count($data)) {
				foreach ($data as $row) {
					$i++;
		?>
		<tr class="<?php echo $i%2 == 0 ? 'odd' : 'even'; ?>">
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name); ?></td>
			<td style="white-space: normal"><?php echo html::anchor('extinfo/details/service/'.$row->host_name.'/?service='.$row->service_description, $row->service_description); ?>&nbsp;</td>
			<td><?php echo date('Y-m-d H:i:s',$row->last_check); ?></td>
			<td><?php echo date('Y-m-d H:i:s',$row->next_check); ?></td>
			<td>
				<?php
					if($row->check_type == nagstat::CHECK_OPTION_NONE)
						echo $this->translate->_('Normal');
					else{
						if($row->check_type == nagstat::CHECK_OPTION_FORCE_EXECUTION)
							echo $this->translate->_('Forced');
						if($row->check_type == nagstat::CHECK_OPTION_FRESHNESS_CHECK)
							echo $this->translate->_('Freshness');
						if($row->check_type == nagstat::CHECK_OPTION_ORPHAN_CHECK)
							echo $this->translate->_('Orphan');
					}
				?>
			</td>
			<td>
				<?php
					echo html::image($this->add_path('icons/16x16/shield-'.($row->active_checks_enabled == true ? 'ok' : 'error').'.png'), array('alt' => 'Enabled', 'title' => 'Enabled', 'style' => 'float: left')).' &nbsp;';
					echo $row->active_checks_enabled == true ? $this->translate->_('ENABLED') : $this->translate->_('DISABLED');
				?>
			</td>
			<td class="icon">
				<?php
					if ($row->active_checks_enabled == true)
						echo html::anchor('command/submit?cmd_typ=DISABLE_HOST_CHECK&host='.urlencode($row->host_name),html::image($this->add_path('icons/16x16/disable-active-checks.png'), array('alt' => $this->translate->_('Disable active checks of this host'), 'title' => $this->translate->_('Disable active checks of this host'))),array('style' => 'border: 0px')).'&nbsp; ';
					else
						echo html::anchor('command/submit?cmd_typ=ENABLE_HOST_CHECK&host='.urlencode($row->host_name),html::image($this->add_path('icons/16x16/enable.png'), array('alt' => $this->translate->_('Enable active checks of this host'), 'title' => $this->translate->_('Enable active checks of this host'))),array('style' => 'border: 0px')).'&nbsp; ';

					echo html::anchor('command/submit?cmd_typ=SCHEDULE_HOST_CHECK&host='.urlencode($row->host_name),html::image($this->add_path('icons/16x16/re-schedule.png'), array('alt' => $this->translate->_('Re-schedule this host check'), 'title' => $this->translate->_('Re-schedule this host check'))),array('style' => 'border: 0px'));
				?>
			</td>
		</tr>
		<?php } } else { ?>
		<tr class="even">
			<td colspan="7"><?php echo $this->translate->_('Nothing scheduled'); ?></td>
		</tr>
		<?php } ?>
	</table>
</div>

<?php $this->session->set('back_extinfo',$back_link);?>