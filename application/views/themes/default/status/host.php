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
	<table id="host_table" style="margin-bottom: 10px">
	<caption style="margin-top: -15px"><?php echo $sub_title ?></caption>
		<thead>
			<tr>
				<?php
					$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
					$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'host_name';
					$n = 0;
					foreach($header_links as $row) {
						$n++;
						if (isset($row['url_desc'])) {
							if ($n == 3)
								echo '<th class="no-sort">'.$t->_('Actions').'</th>';
							echo '<th '.($row['title'] == 'Host' ? 'colspan="2"' : '').' class="header'.(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' : (($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' : (isset($row['url_desc']) ? '' : 'None'))) .
								'" onclick="location.href=\'' . Kohana::config('config.site_domain') . '/index.php/'.((isset($row['url_desc']) && $order == 'ASC') ? str_replace('&','&amp;',$row['url_desc']) : ((isset($row['url_asc']) && $order == 'DESC') ? str_replace('&','&amp;',$row['url_asc']) : '')).'\'">';
							echo ($row['title'] == 'Status' ? '' : $row['title']);
							echo '</th>';
						}
					}
				?>
				<th><?php echo $t->_('Status information') ?></th>
			</tr>
		</thead>
		<tbody>
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
				<td class="icon bl">
					&nbsp;<?php echo html::anchor('extinfo/details/host/'.$row->host_name,html::image($this->add_path('icons/16x16/shield-'.strtolower(Current_status_Model::status_text($row->current_state, Router::$method)).'.png'),array('alt' => Current_status_Model::status_text($row->current_state, Router::$method), 'title' => $t->_('Host status').': '.Current_status_Model::status_text($row->current_state, Router::$method))), array('style' => 'border: 0px')); ?>
				</td>
				<td>
					<div style="float: left"><?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars($row->host_name)); ?></div>
				<?php	$host_comments = Comment_Model::count_comments($row->host_name);
						if ($host_comments!=0) { ?>
					<span style="float: right">
						<?php echo html::anchor('extinfo/details/host/'.$row->host_name.'#comments',
								html::image($this->add_path('icons/16x16/add-comment.png'),
								array('alt' => sprintf($t->_('This host has %s comment(s) associated with it'), $host_comments),
								'title' => sprintf($t->_('This host has %s comment(s) associated with it'), $host_comments))), array('style' => 'border: 0px')); ?>
					</span>
					<?php } ?>
					<div style="float: right">
					<?php
						if ($row->problem_has_been_acknowledged)
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => $t->_('Acknowledged'), 'title' => $t->_('Acknowledged'))), array('style' => 'border: 0px'));
						if (empty($row->notifications_enabled))
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => $t->_('Notification enabled'), 'title' => $t->_('Notification disabled'))), array('style' => 'border: 0px'));
						if (!$row->active_checks_enabled)
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => $t->_('Active checks enabled'), 'title' => $t->_('Active checks disabled'))), array('style' => 'border: 0px'));
						if (isset($row->is_flapping) && $row->is_flapping)
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => $t->_('Flapping'), 'title' => $t->_('Flapping'), 'style' => 'margin-bottom: -2px')), array('style' => 'border: 0px'));
						if ($row->scheduled_downtime_depth > 0)
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image($this->add_path('icons/16x16/downtime.png'),array('alt' => $t->_('Scheduled downtime'), 'title' => $t->_('Scheduled downtime'))), array('style' => 'border: 0px'));
					?>
					</div>
				</td>
				<td class="icon">
				<?php if (!empty($row->icon_image)) {
					echo html::anchor('extinfo/details/host/'.$row->host_name,html::image('application/media/images/logos/'.$row->icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $row->icon_image_alt, 'title' => $row->icon_image_alt)),array('style' => 'border: 0px'));
				} ?>
				</td>
				<td class="icon" style="text-align: left">
					<?php
						echo html::anchor('status/service/'.$row->host_name,html::image($this->add_path('icons/16x16/service-details.gif'), $t->_('View service details for this host')), array('style' => 'border: 0px')).' &nbsp;';
						if (nacoma::link()===true)
							echo nacoma::link('configuration/configure/host/'.$row->host_name, 'icons/16x16/nacoma.png', $t->_('Configure this host')).' &nbsp;';
						if (Kohana::config('config.pnp4nagios_path')!==false)
							echo (pnp::has_graph($row->host_name))  ? '<a href="' . Kohana::config('config.site_domain') . '/index.php/pnp/?host='.urlencode($row->host_name).'" style="border: 0px">'.html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => 'Show performance graph', 'title' => 'Show performance graph')).'</a> &nbsp;' : '';
						if (!empty($row->action_url)) {
							echo '<a href="'.nagstat::process_macros($row->action_url, $row).'" style="border: 0px" target="_blank">';
							echo html::image($this->add_path('icons/16x16/host-actions.png'), $t->_('Perform extra host actions'));
							echo '</a> &nbsp;';
						}
						if (!empty($row->notes_url)) {
							echo '<a href="'.nagstat::process_macros($row->notes_url, $row).'" style="border: 0px" target="_blank">';
							echo html::image($this->add_path('icons/16x16/host-notes.png'), $t->_('View extra host notes'));
							echo '</a>';
						}
					?>
				</td>
				<td style="white-space: normal"><?php echo $row->last_check ? date('Y-m-d H:i:s',$row->last_check) : $na_str ?></td>
				<td><?php echo $row->duration != $row->cur_time ? time::to_string($row->duration) : $na_str ?></td>
				<td style="white-space: normal">
					<?php
					if ($row->current_state == Current_status_Model::HOST_PENDING)
						echo $row->should_be_scheduled ? sprintf($pending_output, date(nagstat::date_format(), $row->next_check)) : $nocheck_output;
					else
						echo str_replace('','',$row->output);
					?>
				</td>
			</tr>
			<?php	} ?>
		</tbody>
	</table>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<br /><br />
</div>
