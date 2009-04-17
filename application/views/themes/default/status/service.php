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

<table style="border-spacing: 1px">
	<tr>
		<th>&nbsp;</th>
		<?php	foreach ($header_links as $row) { ?>
			<th>
				<?php echo $row['title'] ?>&nbsp;
				<?php echo isset($row['url_asc']) ? html::anchor($row['url_asc'], html::image($row['img_asc'], array('alt' => $row['alt_asc'], 'title' => $row['alt_asc'], 'border' => 0))) : '' ?>
				<?php echo isset($row['url_desc']) ? html::anchor($row['url_desc'], html::image($row['img_desc'], array('alt' => $row['alt_desc'], 'title' => $row['alt_desc'], 'border' => 0))) : '' ?>
			</th>
		<?php	} ?>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
	</tr>
<?php
	$curr_host = false;
	$a = 0;
	foreach ($result as $row) {

		# set status classes
		# row "striping" done by JQuery?
		$status_class = 'status';
		$status_bg_class = '';
		switch ($row->current_state) {
			case Current_status_Model::SERVICE_PENDING :
				$status_class .= ' pending';
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
		<td class="<?php echo ($curr_host != $row->host_name) ? $host_status_bg_class : 'white' ?>" <?php echo ($curr_host != $row->host_name) ? '' : 'colspan="2"' ?>>
			<?php
			if ($curr_host != $row->host_name) { ?>
			<table border=0 width=100% cellpadding=0 cellspacing=0>
				<tr>
					<td class="<?php echo $host_status_bg_class ?>">
						<table border=0 cellpadding=0 cellspacing=0>
							<tr>
								<td nowrap='nowrap' class="<?php echo $host_status_bg_class ?>">
									<?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars($row->host_name)) ?>
								</td>
							</tr>
						</table>
					</td>
					<td align="right" class="<?php echo $host_status_bg_class ?>">
						<table border=0 cellpadding=0 cellspacing=0>
							<tr>
						<?php	if ($row->problem_has_been_acknowledged) { ?>
								<td align="center" class="<?php echo $host_status_bg_class ?>">
									<?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('ACK')) ?>
								</td>
						<?php	}
								if (empty($row->notifications_enabled)) { ?>
								<td class="<?php echo $host_status_bg_class ?>">
									<?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('nDIS')) ?>
								</td>
						<?php	}
								if (!$row->active_checks_enabled) { ?>
								<td class="<?php echo $host_status_bg_class ?>">
									<?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('DIS')) ?>
								</td>
						<?php	}
								if (isset($row->is_flapping) && $row->is_flapping) { ?>
								<td class="<?php echo $host_status_bg_class ?>">
									<?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('FPL')) ?>
								</td>
						<?php	}
								if ($row->scheduled_downtime_depth > 0) { ?>
								<td class="<?php echo $host_status_bg_class ?>">
									<?php echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('SDT')) ?>
								</td>
						<?php	}
								if (!empty($row->notes_url)) { ?>
								<td class="<?php echo $host_status_bg_class ?>">
									<a href="<?php echo $row->notes_url ?>" target="_blank" title="View Extra Host Notes">
										<img src="/monitor/images/notes.gif" border=0 alt="View Extra Host Notes" />
									</a>
								</td>
						<?php	}
								if (!empty($row->action_url)) { ?>
								<td class="<?php echo $host_status_bg_class ?>">
									<a href="<?php echo $row->action_url ?>" title="Perform Extra Host Actions">
										<img src="/monitor/images/action.gif" border=0 title="Perform Extra Host Actions" />
									</a>
								</td>
						<?php	}
								if (!empty($row->icon_image)) { ?>
								<td class="<?php echo $host_status_bg_class ?>">
									<img src="<?php echo $logos_path.$row->icon_image ?>" WIDTH=20 HEIGHT=20 border=0 alt="View Extra Host Notes" />
								</td>
						<?php	} ?>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
		<td class="<?php echo ($curr_host != $row->host_name) ? 'w80' : 'white' ?>">
			<?php
			if ($curr_host != $row->host_name) { ?>
					<?php echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars($row->host_name)) ?>
					<div style="float: right">
						<?php
							if ($row->problem_has_been_acknowledged) {
								echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('ACK'));
							}
							if (empty($row->notifications_enabled)) {
								echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('nDIS'));
							}
							if (!$row->active_checks_enabled) {
								echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('DIS'));
							}
							if (isset($row->is_flapping) && $row->is_flapping) {
								echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('FPL'));
							}
							if ($row->scheduled_downtime_depth > 0) {
								echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('SDT'));
							}
						?>
					</div>
				</td>
				<td style="width: 16px"><?php if (!empty($row->notes_url)) { ?>
					<a href="<?php echo $row->notes_url ?>" title="View Extra Host Notes">
						<img src="/monitor/images/notes.gif" alt="View Extra Host Notes" />
					</a>
				<?php	}	if (!empty($row->action_url)) { ?>
					<a href="<?php echo $row->action_url ?>" title="Perform Extra Host Actions">
						<img src="/monitor/images/action.gif" title="Perform Extra Host Actions" />
					</a>
				<?php	} if (!empty($row->icon_image)) { ?>
					<img src="<?php echo $logos_path.$row->icon_image ?>" alt="View Extra Host Notes" />
				<?php	} ?>
				<?php } ?>
		</td>
		<td class="<?php echo $status_class ?>">
			<?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower(Current_status_Model::translate_status($row->current_state, Router::$method)).'.png',Current_status_Model::translate_status($row->current_state, Router::$method)) ?>
			<?php //echo Current_status_Model::translate_status($row->current_state, Router::$method) ?>
		</td>
		<td style="width: 80px">
			<?php echo html::anchor('extinfo/details/service/'.$row->host_name.'/'.link::encode($row->service_description), html::specialchars($row->service_description)) ?>
		</td>
		<td><?php echo $row->last_check ?></td>
		<td><?php echo $row->duration ?></td>
		<td><?php echo $row->plugin_output ?></td>
		<td style="width: 16px">
			<a href="/monitor/op5/webconfig/edit.php?obj_type=<?php echo Router::$method ?>&amp;host=<?php echo $row->host_name ?>&amp;service=<?php echo str_replace(' ','%20',$row->service_description) ?>">
				<img src='/monitor/images/op5tools/webconfig.png' alt="<?php echo $this->translate->_('Configure this service') ?>" title="<?php echo $this->translate->_('Configure this service') ?>" style="vertical-align: middle; margin: 0px 5px" />
			</a>
		</td>
	</tr>

<?php
		$curr_host = $row->host_name;
	} ?>
</table>

<div id="status_count_summary"><?php echo sizeof($result) ?> Matching Service Entries Displayed</div>
</div>
