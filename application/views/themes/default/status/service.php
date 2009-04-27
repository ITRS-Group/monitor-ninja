<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div class="widget collapsable left w98" id="status_service">
<div id="status_msg" class="widget-header"><?php echo $sub_title ?></div>

<table style="border-spacing: 0px; background-color: #dcdccd" id="sort-table">
	<thead>
	<tr>
		<th class="no-sort">&nbsp;</th>
		<th><?php echo $this->translate->_('Host') ?></th>
		<th><?php echo $this->translate->_('') ?></th>
		<th><?php echo $this->translate->_('Service') ?></th>
		<th><?php echo $this->translate->_('Last check') ?></th>
		<th><?php echo $this->translate->_('Duration') ?></th>
		<th class="no-sort"><?php echo $this->translate->_('Status information') ?></th>
		<th colspan="4"><?php echo $this->translate->_('Actions') ?></th>
		<?php //echo isset($row['url_asc']) ? html::anchor($row['url_asc'], html::image($row['img_asc'], array('alt' => $row['alt_asc'], 'title' => $row['alt_asc']))) : '' ?>
		<?php //echo isset($row['url_desc']) ? html::anchor($row['url_desc'], html::image($row['img_desc'], array('alt' => $row['alt_desc'], 'title' => $row['alt_desc']))) : '' ?>
	</tr>
	</thead>
	<tbody>
<?php
	$curr_host = false;
	$a = 0;
	if (!empty($result)) {
		foreach ($result as $row) {

			# set status classes
			# row "striping" done by JQuery?
			$status_class = ''; // status
			$status_bg_class = '';
			switch ($row->current_state) {
				case Current_status_Model::SERVICE_PENDING :
					$status_class .= ' pedning';
					break;
				case Current_status_Model::SERVICE_OK :
					$status_class .= ' ok';
					break;
				case Current_status_Model::SERVICE_WARNING :
					$status_class .= ' warning';
					if ($row->problem_has_been_acknowledged) {
						# using Nagios default here
						$status_bg_class='bg warningack';
					} elseif ($row->scheduled_downtime_depth>0) {
						$status_bg_class='bg warningsched';
					} else {
						$status_bg_class='bg warning';
					}
					break;
				case Current_status_Model::SERVICE_UNKNOWN :
					$status_class .= ' unknown';
					if ($row->problem_has_been_acknowledged) {
						$status_bg_class='bg warningack';
					} elseif ($row->scheduled_downtime_depth>0) {
						$status_bg_class='bg warningsched';
					} else {
						$status_bg_class='bg unknown';
					}
					break;
				case Current_status_Model::SERVICE_CRITICAL :
					$status_class .= ' critical';
					if ($row->problem_has_been_acknowledged) {
						$status_bg_class='bg warningack';
					} elseif ($row->scheduled_downtime_depth>0) {
						$status_bg_class='bg warningsched';
					} else {
						$status_bg_class='bg critical';
					}
					break;
			}

			$host_status_bg_class = 'status';
			switch ($row->host_state) {
			 case Current_status_Model::HOST_DOWN:
				if ($row->hostproblem_is_acknowledged) {
					# using Nagios default here
					$host_status_bg_class .= ' hostdownack';
				} elseif ($row->hostscheduled_downtime_depth>0) {
					$host_status_bg_class .= ' hostdownsched';
				} else {
					$host_status_bg_class .= ' hostdown';
				}
				break;
			 case Current_status_Model::HOST_UNREACHABLE:
				if ($row->hostproblem_is_acknowledged) {
					$host_status_bg_class .= ' hostunreachableack';
				} elseif ($row->hostscheduled_downtime_depth>0) {
					$host_status_bg_class .= ' hostunreachablesched';
				} else {
					$host_status_bg_class .= ' hostunreachable';
				}
				break;
			 default:
				$host_status_bg_class .= ' ok';
		}
		$a++;
	?>
	<tr class="<?php echo ($a %2 == 0) ? 'odd' : 'even'; ?>">
		<td class="bl <?php echo ($curr_host != $row->host_name) ? 'bt '.$host_status_bg_class : 'white' ?>" <?php //echo ($curr_host != $row->host_name) ? '' : 'colspan="2"' ?>>
			<?php
				if ($curr_host != $row->host_name) {
					echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($row->host_state, Router::$method)).'.png',array('alt' => Current_status_Model::status_text($row->host_state, Router::$method), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($row->host_state, Router::$method)));
				}
			?>
		</td>
		<td class="<?php echo ($curr_host != $row->host_name) ? 'w80' : 'white' ?>">
			<?php
			if ($curr_host != $row->host_name) { ?>
					<?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars($row->host_name)) ?>
					<div style="float: right">
						<?php
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
					</div>
				</td>
			<?php } ?>
		<td class="bl <?php echo $status_class ?>">
			<?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($row->current_state, Router::$method)).'.png',array('alt' => Current_status_Model::status_text($row->current_state, Router::$method), 'title' => $this->translate->_('Service status').': '.Current_status_Model::status_text($row->current_state, Router::$method))) ?>
			<?php //echo Current_status_Model::status_text($row->current_state, Router::$method) ?>
		</td>
		<td><?php echo html::anchor('extinfo/details/service/'.$row->host_name.'/'.link::encode($row->service_description), html::specialchars($row->service_description)) ?></td>
		<td><?php echo $row->last_check ?></td>
		<td><?php echo $row->duration ?></td>
		<td><?php echo $row->plugin_output ?></td>
		<td class="icon">
		<?php	if (!empty($row->action_url)) { ?>
			<a href="<?php echo $row->action_url ?>" style="border: 0px"><img src="/monitor/images/action.gif" alt="<?php echo $this->translate->_('Perform extra host actions');?>" title="<?php echo $this->translate->_('Perform extra host actions');?>" /></a>
		<?php	} ?>
		</td>
		<td class="icon">
		<?php	if (!empty($row->icon_image)) { ?>
			<img src="<?php echo $logos_path.$row->icon_image ?>" alt="<?php echo $this->translate->_('View extra host notes');?>" title="<?php echo $this->translate->_('View extra host notes');?>" />
		<?php	} ?>
		</td>
		<td class="icon">
			<?php if (!empty($row->notes_url)) { ?>
			<a href="<?php echo $row->notes_url ?>" style="border: 0px"><img src="/monitor/images/notes.gif" alt="<?php echo $this->translate->_('View extra host notes');?>" title="<?php echo $this->translate->_('View extra host notes');?>" /></a>
			<?php } ?>
		</td>
		<td class="icon">
			<a href="/monitor/op5/webconfig/edit.php?obj_type=<?php echo Router::$method ?>&amp;host=<?php echo $row->host_name ?>&amp;service=<?php echo str_replace(' ','%20',$row->service_description) ?>" style="border: 0px">
				<img src='/monitor/images/op5tools/webconfig.png' alt="<?php echo $this->translate->_('Configure this service') ?>" title="<?php echo $this->translate->_('Configure this service') ?>" />
			</a>
		</td>
	</tr>

	<?php
			$curr_host = $row->host_name;
		} ?>
		</tbody>
	</table>

	<div id="status_count_summary"><?php echo sizeof($result) ?> Matching Service Entries Displayed</div>
<?php } ?>
</div>
