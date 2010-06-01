<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
<div class="widget left w32" id="page_links">
		<ul>
			<li><?php echo $t->_('View').', '.$label_view_for.':'; ?></li>
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

<div class="widget left w98" id="status_host">
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<?php echo form::open('command/multi_action'); ?><br />
	<table id="host_table" style="margin-bottom: 10px">
	<caption style="margin-top: 0px"><?php echo $sub_title ?>: <?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_items" style="font-weight: normal"><?php echo $this->translate->_('Select Multiple Items') ?></a><br /></caption>

			<tr>
				<?php
					$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
					$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'host_name';
					$n = 0;
					foreach($header_links as $row) {
						$n++;
						if (isset($row['url_desc'])) {
							echo ($n == 2 ? '<th class="item_select"><input type="checkbox" class="select_all_items" title="'.$this->translate->_('Click to select/unselect all').'"></th>' : '')."\n";
							echo ($n == 3 ? '<th class="no-sort">'.$t->_('Actions').'</th>' : '')."\n";
							echo '<th '.($row['title'] == 'Host' ? 'colspan="2"' : '').' class="header'.(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' : (($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' : (isset($row['url_desc']) ? '' : 'None'))) .
								'" onclick="location.href=\'' . url::site() .((isset($row['url_desc']) && $order == 'ASC') ? str_replace('&','&amp;',$row['url_desc']) : ((isset($row['url_asc']) && $order == 'DESC') ? str_replace('&','&amp;',$row['url_asc']) : '')).'\'">'."\n";
							echo ($n == 1 ? '' : $row['title']);
							echo '</th>'."\n";
						}
					}
				?>
				<th><?php echo $t->_('Status information') ?></th>
			</tr>


		<?php
# Do not, under ANY circumstances, remove the if-clause below.
# Doing so results in a Kohana error if no hosts are found. That
# is a VERY, VERY BAD THING, so please pretty please leave it where
# it is (yes, I'm talking to you, My).
if (empty($result)) {
	$result = array();
}
$a = 0;
foreach ($result as $row) {
	$a++;
		?>
			<tr class="<?php echo ($a %2 == 0) ? 'odd' : 'even'; ?>">
				<td class="icon bl <?php echo strtolower(Current_status_Model::status_text($row->current_state, Router::$method)); ?>">&nbsp;</td>
				<td class="item_select"><?php echo form::checkbox(array('name' => 'object_select[]'), $row->host_name); ?></td>
				<td>
					<div style="float: left"><?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars($row->host_name)); ?></div>
					<div style="float: right">
					<?php
						$properties = 0;
						if ($row->problem_has_been_acknowledged) {
							echo '&nbsp;'.html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => $t->_('Acknowledged'), 'title' => $t->_('Acknowledged'))), array('style' => 'border: 0px'));
							$properties++;
						}
						if (empty($row->notifications_enabled)) {
							echo '&nbsp;'.html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => $t->_('Notification disabled'), 'title' => $t->_('Notification disabled'))), array('style' => 'border: 0px'));
							$properties += 2;
						}
						if (!$row->active_checks_enabled) {
							echo '&nbsp;'.html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => $t->_('Active checks enabled'), 'title' => $t->_('Active checks disabled'))), array('style' => 'border: 0px'));
							$properties += 4;
						}
						if (isset($row->is_flapping) && $row->is_flapping)
							echo '&nbsp;'.html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => $t->_('Flapping'), 'title' => $t->_('Flapping'), 'style' => 'margin-bottom: -2px')), array('style' => 'border: 0px'));
						if ($row->scheduled_downtime_depth > 0) {
							echo '&nbsp;'.html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/scheduled-downtime.png'),array('alt' => $t->_('Scheduled downtime'), 'title' => $t->_('Scheduled downtime'))), array('style' => 'border: 0px'));
							$properties += 8;
						}
						if ($host_comments !== false && array_key_exists($row->host_name, $host_comments)) {
							echo '&nbsp;'.html::anchor('extinfo/details/host/'.$row->host_name.'#comments',
								html::image($this->add_path('icons/16x16/add-comment.png'),
								array('alt' => sprintf($t->_('This host has %s comment(s) associated with it'), $host_comments[$row->host_name]),
								'title' => sprintf($t->_('This host has %s comment(s) associated with it'), $host_comments[$row->host_name]))), array('style' => 'border: 0px'));
						}
						if ($row->current_state == Current_status_Model::HOST_DOWN || $row->current_state == Current_status_Model::HOST_UNREACHABLE) {
							$properties += 16;
						}
					?><span class="obj_prop" style="display:none"><?php echo $properties ?></span>
					</div>
				</td>
				<td class="icon">
				<?php if (!empty($row->icon_image)) {
					echo html::anchor('extinfo/details/host/'.$row->host_name,html::image('application/media/images/logos/'.$row->icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $row->icon_image_alt, 'title' => $row->icon_image_alt)),array('style' => 'border: 0px'));
				} ?>
				</td>
				<td style="width: 105px">
					<?php
						echo html::anchor('status/service/'.$row->host_name,html::image($this->add_path('icons/16x16/service-details.gif'), array('alt' => $t->_('View service details for this host'), 'title' => $t->_('View service details for this host'))), array('style' => 'border: 0px')).' &nbsp;';
						if (nacoma::link()===true)
							echo nacoma::link('configuration/configure/host/'.$row->host_name, 'icons/16x16/nacoma.png', $t->_('Configure this host')).' &nbsp;';
						if (Kohana::config('config.pnp4nagios_path')!==false)
							echo (pnp::has_graph($row->host_name))  ? html::anchor('pnp/?host='.urlencode($row->host_name), html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => 'Show performance graph', 'title' => 'Show performance graph')), array('style' => 'border: 0px')).'&nbsp;' : '';
						if (!empty($row->action_url)) {
							echo '<a href="'.nagstat::process_macros($row->action_url, $row).'" style="border: 0px" target="_blank">';
							echo html::image($this->add_path('icons/16x16/host-actions.png'), array('alt' => $t->_('Perform extra host actions'), 'title' => $t->_('Perform extra host actions')));
							echo '</a> &nbsp;';
						}
						if (!empty($row->notes_url)) {
							echo '<a href="'.nagstat::process_macros($row->notes_url, $row).'" style="border: 0px" target="_blank">';
							echo html::image($this->add_path('icons/16x16/host-notes.png'), array('alt' => $t->_('View extra host notes'), 'title' => $t->_('View extra host notes')));
							echo '</a>';
						}
					?>
				</td>
				<td style="white-space: normal; width: 110px"><?php echo $row->last_check ? date('Y-m-d H:i:s',$row->last_check) : $na_str ?></td>
				<td style="width: 110px"><?php echo $row->duration != $row->cur_time ? time::to_string($row->duration) : $na_str ?></td>
				<td style="white-space: normal">
					<?php
					if ($row->current_state == Current_status_Model::HOST_PENDING)
						echo $row->should_be_scheduled ? sprintf($pending_output, date(nagstat::date_format(), $row->next_check)) : $nocheck_output;
					else {
						$output = nl2br($row->output.' '.$row->long_output);
						echo str_replace('','', $output);
					}
					?>
				</td>
			</tr>
			<?php	} ?>

	</table>
	<?php echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select', 'id' => 'multi_action_select'),
		array(
			'' => $this->translate->_('Select Action'),
			'SCHEDULE_HOST_DOWNTIME' => $this->translate->_('Schedule Downtime'),
			'ACKNOWLEDGE_HOST_PROBLEM' => $this->translate->_('Acknowledge'),
			'REMOVE_HOST_ACKNOWLEDGEMENT' => $this->translate->_('Remove Problem Acknowledgement'),
			'DISABLE_HOST_NOTIFICATIONS' => $this->translate->_('Disable Host Notifications'),
			'ENABLE_HOST_NOTIFICATIONS' => $this->translate->_('Enable Host Notifications'),
			'DISABLE_HOST_SVC_NOTIFICATIONS' => $this->translate->_('Disable Notifications For All Services'),
			'DISABLE_HOST_CHECK' => $this->translate->_('Disable Active Checks'),
			'ENABLE_HOST_CHECK' => $this->translate->_('Enable Active Checks')
			)
		); ?>
	<?php echo form::submit(array('id' => 'multi_object_submit', 'class' => 'item_select', 'value' => $this->translate->_('Submit'))); ?>
	<?php echo form::hidden('obj_type', 'host'); ?>
	<?php echo form::close(); ?>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<br /><br />
</div>