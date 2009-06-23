<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>
<div class="widget left w32" id="page_links">
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

<div class="widget left w98" id="status_host">
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<table id="host_table" style="table-layout: fixed; margin-bottom: 10px">
	<caption style="margin-top: -15px"><?php echo $sub_title ?></caption>
		<colgroup>
			<col style="width: 30px" />
			<col style="width: 200px" />
			<col style="width: 30px" />
			<col style="width: 122px" />
			<col style="width: 105px" />
			<col style="width: 100%" />
			<col style="width: 30px" />
			<col style="width: 30px" />
			<?php if (Kohana::config('config.pnp4nagios_path')!==false) { ?>
			<col style="width: 30px" />
			<?php } if (Kohana::config('config.nacoma_path')!==false) { ?>
			<col style="width: 30px" />
			<?php } ?>
		</colgroup>
		<thead>
			<tr>
				<?php
				$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
				$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'host_name';

				foreach($header_links as $row) {
					if (isset($row['url_desc'])) {
						echo '<th '.($row['title'] == 'Host' ? 'colspan="2"' : '').' class="header'.(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' : (($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' : (isset($row['url_desc']) ? '' : 'None'))).'"
									onclick="location.href=\'/ninja/index.php/'.((isset($row['url_desc']) && $order == 'ASC') ? $row['url_desc'] : ((isset($row['url_asc']) && $order == 'DESC') ? $row['url_asc'] : '')).'\'">';
						echo ($row['title'] == 'Status' ? '' : $row['title']);
						echo '</th>';
					}
				}
			?>
				<th><?php echo $this->translate->_('Status information') ?></th>
				<th class="no-sort" colspan="<?php echo ((Kohana::config('config.nacoma_path')!==false) && (Kohana::config('config.pnp4nagios_path')!==false)) ? 4 : (((Kohana::config('config.nacoma_path')!==false) || (Kohana::config('config.pnp4nagios_path')!==false)) ? 3 : 2); ?>"><?php echo $this->translate->_('Actions') ?></th>
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
					&nbsp;<?php echo html::anchor('extinfo/details/host/'.$row->host_name,html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($row->current_state, Router::$method)).'.png',array('alt' => Current_status_Model::status_text($row->current_state, Router::$method), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($row->current_state, Router::$method))), array('style' => 'border: 0px')); ?>
				</td>
				<td>
					<div style="float: left"><?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars($row->host_name)); ?></div>
					<div style="float: right">
					<?php
						if ($row->problem_has_been_acknowledged) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image('application/views/themes/default/icons/16x16/acknowledged.png',array('alt' => $this->translate->_('Acknowledged'), 'title' => $this->translate->_('Acknowledged'))), array('style' => 'border: 0px'));
						}
						if (empty($row->notifications_enabled)) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image('application/views/themes/default/icons/16x16/notify.png',array('alt' => $this->translate->_('Notification enabled'), 'title' => $this->translate->_('Notification enabled'))), array('style' => 'border: 0px'));
						}
						if (!$row->active_checks_enabled) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image('application/views/themes/default/icons/16x16/active-checks-enabled.png',array('alt' => $this->translate->_('Active checks enabled'), 'title' => $this->translate->_('Active checks enabled'))), array('style' => 'border: 0px'));
						}
						if (isset($row->is_flapping) && $row->is_flapping) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image('application/views/themes/default/icons/16x16/flapping.gif',array('alt' => $this->translate->_('Flapping'), 'title' => $this->translate->_('Flapping'), 'style' => 'margin-bottom: -2px')), array('style' => 'border: 0px'));
						}
						if ($row->scheduled_downtime_depth > 0) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image('application/views/themes/default/icons/16x16/downtime.png',array('alt' => $this->translate->_('Scheduled downtime'), 'title' => $this->translate->_('Scheduled downtime'))), array('style' => 'border: 0px'));
						}
					?>
					</div>
				</td>
				<td class="icon" style="width: 10px">
				<?php if (!empty($row->icon_image)) {
					echo html::anchor('extinfo/details/host/'.$row->host_name,html::image('application/media/images/logos/'.$row->icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $row->icon_image_alt, 'title' => $row->icon_image_alt)),array('style' => 'border: 0px'));
				} ?>
				</td>
				<td><?php echo date('Y-m-d H:i:s',$row->last_check) ?></td>
				<td><?php echo time::to_string($row->duration) ?></td>
				<td style="white-space: normal"><?php echo str_replace('','',$row->output) ?></td>
				<td class="icon">
					<?php echo html::anchor('status/service/'.$row->host_name,html::image('/application/views/themes/default/icons/16x16/service-details.gif', $this->translate->_('View service details for this host')), array('style' => 'border: 0px')) ?>
				</td>
				<td class="icon">
				<?php if (!empty($row->action_url)) { ?>
					<a href="<?php echo $row->action_url ?>" style="border: 0px">
						<?php echo html::image('/application/views/themes/default/icons/16x16/host-actions.png', $this->translate->_('Perform extra host actions')) ?>
					</a>
				<?php	} if (!empty($row->notes_url)) { ?>
					<a href="<?php echo $row->notes_url ?>" style="border: 0px">
						<?php echo html::image('application/views/themes/default/icons/16x16/host-notes.png', $this->translate->_('View extra host notes')) ?>
					</a>
				<?php	} ?>
				</td>
				<?php if (Kohana::config('config.pnp4nagios_path')!==false) { ?>
				<td class="icon">
					<?php echo (pnp::has_graph($row->host_name))  ? '<a href="/ninja/index.php/pnp/?host='.urlencode($row->host_name).'" style="border: 0px">'.html::image('/application/views/themes/default/icons/16x16/pnp.png', array('alt' => 'Show performance graph', 'title' => 'Show performance graph')).'</a>' : ''; ?>
				</td>
				<?php } ?>
				<?php if (Kohana::config('config.nacoma_path')!==false) { ?>
				<td class="icon">
					<?php echo html::anchor('configuration/configure/host/'.$row->host_name, html::image('/application/views/themes/default/icons/16x16/nacoma.png',$this->translate->_('Configure this host')));?>
				</td>
				<?php } ?>
			</tr>
			<?php	} ?>
		</tbody>
	</table>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<br /><br />
</div>