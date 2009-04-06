<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div class="statusTitle" id="status_msg"><?php echo $sub_title ?></div>

<table border=0 class='status' width=100%>
	<tr>
<?php	foreach ($header_links as $row) { ?>
			<th>
				<?php echo $row['title'] ?>&nbsp;
				<?php echo isset($row['url_asc']) ? html::anchor($row['url_asc'], html::image($row['img_asc'], array('alt' => $row['alt_asc'], 'title' => $row['alt_asc'], 'border' => 0))) : '' ?>
				<?php echo isset($row['url_desc']) ? html::anchor($row['url_desc'], html::image($row['img_desc'], array('alt' => $row['alt_desc'], 'title' => $row['alt_desc'], 'border' => 0))) : '' ?>
			</th>
<?php	}
		?>
	</tr>
<?php
	$curr_host = false;
	foreach ($result as $row) {

		# set status classes
		# row "striping" done by JQuery?
		$status_class = 'status';
		$status_bg_class = '';
		switch ($row->current_state) {
			case Current_status_Model::SERVICE_PENDING :
				$status_class .= 'PENDING';
				break;
			case Current_status_Model::SERVICE_OK :
				$status_class .= 'OK';
				break;
			case Current_status_Model::SERVICE_WARNING :
				$status_class .= 'WARNING';
				if ($row->problem_has_been_acknowledged) {
					# using Nagios default here
					$status_bg_class="BGWARNINGACK";
				} elseif ($row->scheduled_downtime_depth>0) {
					$status_bg_class="BGWARNINGSCHED";
				} else {
					$status_bg_class="BGWARNING";
				}
				break;
			case Current_status_Model::SERVICE_UNKNOWN :
				$status_class .= 'UNKNOWN';
				if ($row->problem_has_been_acknowledged) {
					$status_bg_class="BGUNKNOWNACK";
				} elseif ($row->scheduled_downtime_depth>0) {
					$status_bg_class="BGUNKNOWNSCHED";
				} else {
					$status_bg_class="BGUNKNOWN";
				}
				break;
			case Current_status_Model::SERVICE_CRITICAL :
				$status_class .= 'CRITICAL';
				if ($row->problem_has_been_acknowledged) {
					$status_bg_class="BGCRITICALACK";
				} elseif ($row->scheduled_downtime_depth>0) {
					$status_bg_class="BGCRITICALSCHED";
				} else {
					$status_bg_class="BGCRITICAL";
				}
				break;
		}

		$host_status_bg_class = 'status';
		switch ($row->host_state) {
			case Current_status_Model::HOST_DOWN:
				if ($row->hostproblem_is_acknowledged) {
					# using Nagios default here
					$host_status_bg_class .= "HOSTDOWNACK";
				} elseif ($row->hostscheduled_downtime_depth>0) {
					$host_status_bg_class .= "HOSTDOWNSCHED";
				} else {
					$host_status_bg_class .= "HOSTDOWN";
				}
				break;
			case Current_status_Model::HOST_UNREACHABLE:
				if ($row->hostproblem_is_acknowledged) {
					$host_status_bg_class .= "HOSTUNREACHABLEACK";
				} elseif ($row->hostscheduled_downtime_depth>0) {
					$host_status_bg_class .= "HOSTUNREACHABLESCHED";
				} else {
					$host_status_bg_class .= "HOSTUNREACHABLE";
				}
				break;
			default:
				$host_status_bg_class .= 'Even';
		}
	?>
	<tr>
		<td class="<?php echo $host_status_bg_class ?>">
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
			<?php
			} ?>
		</td>
		<td class="statusEven">
			<?php echo html::anchor('extinfo/details/service/'.$row->host_name.'/'.link::encode($row->service_description), html::specialchars($row->service_description)) ?>
			<div align="right" style="display:inline">
			<a href="/monitor/op5/webconfig/edit.php?obj_type=<?php echo Router::$method ?>&host=<?php echo $row->host_name ?>&service=<?php echo $row->service_description ?>">
				<img src='/monitor/images/op5tools/webconfig.png' border=0>
			</a>
			</div>
		</td>
		<td class="<?php echo $status_class ?>"><?php echo Current_status_Model::translate_status($row->current_state, Router::$method) ?></td>
		<td class="statusEven"><?php echo $row->last_check ?></td>
		<td class="statusEven"><?php echo $row->duration ?></td>
		<td class="statusEven"><?php echo $row->plugin_output ?></td>
	</tr>

<?php
		$curr_host = $row->host_name;
	} ?>
</table>

<div id="status_count_summary"><?php echo sizeof($result) ?> Matching Service Entries Displayed</div>