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


<div class="widget left w98" id="status_service">
<?php echo (isset($pagination)) ? $pagination : ''; ?>
<table style="table-layout: fixed" id="service_table">
	<colgroup>
		<col style="width: 30px" />
		<col style="width: 160px" />
		<col style="width: 30px" />
		<col style="width: 160px" />
		<col style="width: 122px" />
		<col style="width: 105px" />
		<col style="width: 100%" />
		<col style="width: 30px" />
		<col style="width: 30px" />
		<!--<col style="width: 30px" />-->
	</colgroup>
	<thead>
		<tr>
			<th>&nbsp;</th>
			<!--<th><?php echo $this->translate->_('Host') ?></th>
			<th><?php echo $this->translate->_('') ?></th>
			<th><?php echo $this->translate->_('Service') ?></th>
			<th><?php echo $this->translate->_('Last check') ?></th>
			<th><?php echo $this->translate->_('Duration') ?></th>
			<th><?php echo $this->translate->_('Status information') ?></th>-->
			<?php
				/*foreach($header_links as $row) {
					echo '<th '.
						//((isset($row['url_asc']) && (str_replace('/ninja/index.php/','',$_SERVER['PHP_SELF']) == $row['url_asc'])) ? 'onclick="location.href=\'/ninja/index.php/'.str_replace('&','&amp;',$row['url_desc']).'\'" class="headerSortUp"' : 'class="header"').
						//(isset($row['url_asc']) ? 'onclick="location.href=\'/ninja/index.php/'.str_replace('&','&amp;',$row['url_asc']).'\'" class="headerSortDown"' : 'class="header"').
						'>'.$row['title'].'</th>';
				}*/
			?>
			<?php
				foreach($header_links as $row) {
					echo '<th><div style="float: left">';
					echo ($row['title'] == 'Status' ? '' : $row['title']);
					echo '</div><div style="float: right">';
					echo isset($row['url_desc']) ? html::anchor($row['url_desc'], html::image($row['img_desc'], array('alt' => $row['alt_desc'], 'title' => $row['alt_desc'])), array('style' => 'border: 0px')) : '';
					echo isset($row['url_asc']) ? html::anchor($row['url_asc'], html::image($row['img_asc'], array('alt' => $row['alt_asc'], 'title' => $row['alt_asc'])), array('style' => 'border: 0px')) : '';
					echo '</div></th>';
				}
			?>
			<th class="no-sort" colspan="2"><?php echo $this->translate->_('Actions') ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$curr_host = false;
	$a = 0;
	if (!empty($result)) {
		foreach ($result as $row) {
		$a++;
	?>
	<tr class="<?php echo ($a %2 == 0) ? 'odd' : 'even'; ?>">
		<td class="icon <?php echo ($curr_host != $row->host_name) ? 'bt' : 'white' ?>" <?php echo ($curr_host != $row->host_name) ? '' : 'colspan="1"' ?>>
			<?php
				if ($curr_host != $row->host_name) {
					echo html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($row->host_state, Router::$method)).'.png',array('alt' => Current_status_Model::status_text($row->host_state, Router::$method), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($row->host_state, Router::$method)));
				}
			?>
		</td>
		<td class="service_hostname <?php echo ($curr_host != $row->host_name) ? 'w80' : 'white' ?>" style="white-space: normal">
			<?php if ($curr_host != $row->host_name) { ?>
				<div style="float: left"><?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars($row->host_name)) ?></div>
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
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image('application/views/themes/default/icons/16x16/flapping.gif',array('alt' => $this->translate->_('Flapping'), 'title' => $this->translate->_('Flapping'))), array('style' => 'border: 0px'));
						}
						if ($row->scheduled_downtime_depth > 0) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::image('application/views/themes/default/icons/16x16/downtime.png',array('alt' => $this->translate->_('Scheduled downtime'), 'title' => $this->translate->_('Scheduled downtime'))), array('style' => 'border: 0px'));
						}
					?>
				</div>
			<?php } ?>
		</td>
		<td class="icon bl">
			<?php echo html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($row->current_state, Router::$method)).'.png',array('alt' => Current_status_Model::status_text($row->current_state, Router::$method), 'title' => $this->translate->_('Service status').': '.Current_status_Model::status_text($row->current_state, Router::$method))) ?>
		</td>
		<td style="white-space: normal"><?php echo html::anchor('extinfo/details/service/'.$row->host_name.'/?service='.$row->service_description, html::specialchars($row->service_description)) ?></td>
		<td><?php echo date('Y-m-d H:i:s',$row->last_check) ?></td>
		<td><?php echo $row->duration ?></td>
		<td style="white-space: normal"><?php echo str_replace('','',$row->plugin_output) ?></td>
		<!--<td class="icon">
		<?php	//if (!empty($row->icon_image)) { ?>
			<?php //echo html::image('application/media/images/logos/'.$row->icon_image,array('alt' => $row->icon_image_alt,'title' => $row->icon_image_alt));?>
		<?php	//} ?>
		</td>-->
		<td class="icon">
		<?php	if (!empty($row->action_url)) { ?>
			<a href="<?php echo $row->action_url ?>" style="border: 0px">
			<?php echo html::image('application/views/themes/default/icons/16x16/host-actions.png',array('alt' => $this->translate->_('Perform extra host actions'),'title' => $this->translate->_('Perform extra host actions')))?></a>
		<?php	} if (!empty($row->notes_url)) { ?>
			<a href="<?php echo $row->notes_url ?>" style="border: 0px">
				<?php echo html::image('/application/views/themes/default/icons/16x16/host-notes.png',array('alt' => $this->translate->_('View extra host notes'),'title' => $this->translate->_('View extra host notes')))?>
			</a>
			<?php } ?>
		</td>
		<td class="icon">
			<?php echo html::anchor('configuration/configure/service/'.$row->host_name.'?service='.urlencode($row->service_description), html::image('/application/views/themes/default/icons/16x16/nacoma.png',array('alt' => $this->translate->_('Configure this service'),'title' => $this->translate->_('Configure this service')))) ?>
		</td>
	</tr>

	<?php
			$curr_host = $row->host_name;
		} ?>
		</tbody>
	</table>


<?php } ?>
<div id="status_count_summary"><?php echo sizeof($result) ?> Matching Service Entries Displayed</div>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>
