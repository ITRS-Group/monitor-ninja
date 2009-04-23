<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div class="widget collapsable left w98" id="status_host">
<div id="status_msg" class="widget-header"><?php echo $sub_title ?></div>

<table style="border-spacing: 1px" id="sort-table">
	<thead>
	<tr>
		<th>&nbsp;</th>
		<th><?php echo $this->translate->_('Host') ?></th>
		<th><?php echo $this->translate->_('Last check') ?></th>
		<th><?php echo $this->translate->_('Duration') ?></th>
		<th><?php echo $this->translate->_('Status information') ?></th>
		<th colspan="5"><?php echo $this->translate->_('Actions') ?></th>
		<?php //echo isset($row['url_asc']) ? html::anchor($row['url_asc'], html::image($row['img_asc'], array('alt' => $row['alt_asc'], 'title' => $row['alt_asc']))) : '' ?>
		<?php //echo isset($row['url_desc']) ? html::anchor($row['url_desc'], html::image($row['img_desc'], array('alt' => $row['alt_desc'], 'title' => $row['alt_desc']))) : '' ?>
	</tr>
	</thead>
	<tbody>
<?php	$a = 0;foreach ($result as $row) {
		$a++;
		# set status classes
		# row "striping" done by JQuery?
		$status_class = 'status';
		$status_bg_class = '';
		switch ($row->current_state) {
			case Current_status_Model::HOST_PENDING:
				$status_class .= 'HOSTPENDING';
				break;
			case Current_status_Model::HOST_UP:
				$status_class .= 'HOSTUP';
				break;
			case Current_status_Model::HOST_DOWN:
				$status_class .= 'HOSTDOWN';
				if ($row->problem_has_been_acknowledged) {
					# using Nagios default here
					$status_bg_class="BGDOWNACK";
				} elseif ($row->scheduled_downtime_depth>0) {
					$status_bg_class="BGDOWNSCHED";
				} else {
					$status_bg_class="BGDOWN";
				}
				break;
			case Current_status_Model::HOST_UNREACHABLE:
				$status_class .= 'HOSTUNREACHABLE';
				if ($row->problem_has_been_acknowledged) {
					$status_bg_class="BGUNREACHABLEACK";
				} elseif ($row->scheduled_downtime_depth>0) {
					$status_bg_class="BGUNREACHABLESCHED";
				} else {
					$status_bg_class="BGUNREACHABLE";
				}
				break;
		}

	?>

	<tr class="<?php echo ($a %2 == 0) ? 'odd' : 'even'; ?>">
		<td class="status icon">
			<?php
				echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($row->current_state, Router::$method)).'.png',array('alt' => Current_status_Model::status_text($row->current_state, Router::$method), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($row->current_state, Router::$method)));
				//echo Current_status_Model::status_text($row->current_state, Router::$method) ?>
		</td>
		<td>
			<?php
				echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars($row->host_name));

				if ($row->problem_has_been_acknowledged) {
					echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('ACK'));
				}
				if (empty($row->notifications_enabled)) {
					echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('nDIS'));
				}
				if (!$row->active_checks_enabled) {
					echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('DIS'));
				}
				if (isset($row->is_flapping) && $row->is_flapping) {
					echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('FPL'));
				}
				if ($row->scheduled_downtime_depth > 0) {
					echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('SDT'));
				}
			?>
		</td>
		<td class="statusEven"><?php echo $row->last_check ?></td>
		<td class="statusEven"><?php echo $row->duration ?></td>
		<td class="statusEven"><?php echo $row->plugin_output ?></td>
		<td class="icon">
			<?php if (!empty($row->notes_url)) { ?>
				<a href="<?php echo $row->notes_url ?>" style="border: 0px">
					<img src="/monitor/images/notes.gif" alt="<?php echo $this->translate->_('View extra host notes') ?>" title="<?php echo $this->translate->_('View extra host notes') ?>" />
				</a>
			<?php	} ?>
		</td>
		<td class="icon">
		<?php if (!empty($row->action_url)) { ?>
			<a href="<?php echo $row->action_url ?>" style="border: 0px">
				<img src="/monitor/images/action.gif" title="<?php echo $this->translate->_('Perform extra host actions') ?>" alt="<?php echo $this->translate->_('Perform extra host actions') ?>" />
			</a>
		<?php	} ?>
		</td>
		<td class="icon">
			<?php echo html::anchor('status/service/'.$row->host_name,'<img src="/monitor/images/status2.gif" alt="View service details for this host" title="View service details for this host" />') ?>
		</td>
		<td class="icon">
			<a href="/monitor/op5/webconfig/edit.php?obj_type=<?php echo Router::$method ?>&amp;host=<?php echo $row->host_name ?>" style="border: 0px">
				<img src='/monitor/images/op5tools/webconfig.png' alt="<?php echo $this->translate->_('Configure this host') ?>" title="<?php echo $this->translate->_('Configure this host') ?>" />
			</a>
		</td>
		<td class="status icon">
		<?php if (!empty($row->icon_image)) { ?>
			<img src="<?php echo $logos_path.$row->icon_image ?>" style="height: 16px"  title="<?php echo $this->translate->_('View extra host notes') ?>"  alt="<?php echo $this->translate->_('View extra host notes') ?>" />
		<?php	} ?>
		</td>
	</tr>

<?php	} ?>
</tbody>
</table>

<div id="status_count_summary"><?php echo sizeof($result).' '.$this->translate->_('Matching Host Entries Displayed'); ?></div>
</div>
