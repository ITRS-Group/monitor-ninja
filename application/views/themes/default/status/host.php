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
<?php	foreach ($result as $row) {

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
	<tr>
		<td class="statusEven">
			<table border=0 width=100% cellpadding=0 cellspacing=0>
				<tr>
					<td class="statusEven">
						<table border=0 cellpadding=0 cellspacing=0>
							<tr>
								<td class="statusEven">
									<?php echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars($row->host_name)) ?>
								</td>
							</tr>
						</table>
					</td>
					<td align=right class="statusEven">
						<table border=0 cellpadding=0 cellspacing=0>
							<tr>
						<?php	if ($row->problem_has_been_acknowledged) { ?>
								<td align="center" class="statusEven">
									<?php echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('ACK')) ?>
								</td>
						<?php	}
								if (empty($row->notifications_enabled)) { ?>
								<td class="statusEven">
									<?php echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('nDIS')) ?>
								</td>
						<?php	}
								if (!$row->active_checks_enabled) { ?>
								<td class="statusEven">
									<?php echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('DIS')) ?>
								</td>
						<?php	}
								if (isset($row->is_flapping) && $row->is_flapping) { ?>
								<td class="statusEven">
									<?php echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('FPL')) ?>
								</td>
						<?php	}
								if ($row->scheduled_downtime_depth > 0) { ?>
								<td class="statusEven">
									<?php echo html::anchor('extinfo/details/host/'.link::encode($row->host_name), html::specialchars('SDT')) ?>
								</td>
						<?php	}
								if (!empty($row->notes_url)) { ?>
								<td class="statusEven">
									<a href="<?php echo $row->notes_url ?>" target="_blank" title="View Extra Host Notes">
										<img src="/monitor/images/notes.gif" border=0 alt="View Extra Host Notes" />
									</a>
								</td>
						<?php	}
								if (!empty($row->action_url)) { ?>
								<td class="statusEven">
									<a href="<?php echo $row->action_url ?>" title="Perform Extra Host Actions">
										<img src="/monitor/images/action.gif" border=0 title="Perform Extra Host Actions" />
									</a>
								</td>
						<?php	}
								if (!empty($row->icon_image)) { ?>
								<td class="statusEven">
									<img src="<?php echo $logos_path.$row->icon_image ?>" WIDTH=20 HEIGHT=20 border=0 alt="View Extra Host Notes" />
								</td>
						<?php	} ?>
								<td class="statusEven">
									<?php echo html::anchor('status/service/'.link::encode($row->host_name),'<img src="/monitor/images/status2.gif" border=0 alt="View Service Details For This Host"title="View Service Details For This Host" />') ?>
								</td>
								<td class="statusEven">
									<a href="/monitor/op5/webconfig/edit.php?obj_type=<?php echo Router::$method ?>&host=<?php echo $row->host_name ?>">
										<img src='/monitor/images/op5tools/webconfig.png' border=0>
									</a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
		<td class="<?php echo $status_class ?>"><?php echo Current_status_Model::translate_status($row->current_state, Router::$method) ?></td>
		<td class="statusEven"><?php echo $row->last_check ?></td>
		<td class="statusEven"><?php echo $row->duration ?></td>
		<td class="statusEven"><?php echo $row->plugin_output ?></td>
	</tr>

<?php	} ?>
</table>

<div id="status_count_summary"><?php echo sizeof($result) ?> Matching Host Entries Displayed</div>