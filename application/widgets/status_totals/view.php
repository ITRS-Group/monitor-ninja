<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm" id="widget-status_host_totals">
	<div class="widget-header">
		<strong><?php echo $host_title ?></strong>
	</div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<div id="widget_status_totals_left">
			<table border=0 cellspacing=0 cellpadding=0>
				<tr>
					<td>
						<table border=0 class='hostTotals' cellpadding="1" cellspacing="1">
							<tr>
							<?php 	foreach ($host_header as $row) { ?>
										<th class="<?php echo $row['th_class'] ?>"><?php echo html::anchor($row['url'], html::specialchars($row['lable']), array('class' => $row['link_class'])) ?></th><?php
									} ?>
							</tr>
							<tr>
								<td id="hostTotalsUP" class='hostTotals<?php echo $total_up ? 'UP' : '' ?>'><?php echo $total_up ?></td>
								<td id="hostTotalsDOWN" class='hostTotals<?php echo $total_down ? 'DOWN' : '' ?>'><?php echo $total_down ?></td>
								<td id="hostTotalsUNREACHABLE" class='hostTotals<?php echo $total_unreachable ? 'UNREACHABLE' : '' ?>'><?php echo $total_unreachable ?></td>
								<td id="hostTotalsPENDING" class='hostTotals<?php echo $total_pending ? 'PENDING' : '' ?>'><?php echo $total_pending ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align=center>
						<table border=0 class='hostTotals' width="100%" cellpadding="0" cellspacing="1">
							<tr>
								<th class='hostTotals'>
									<?php echo html::anchor('status/host/'.$host.'/'.nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE, html::specialchars($label_all_problems)) ?>
								</th>
								<th class='hostTotals'>
									<?php echo html::anchor('status/host/'.$host.'/', html::specialchars($label_all_types)) ?>
								</th>
							</tr>
							<tr>
								<td id="hostTotalsPROBLEMS" class='hostTotals<?php echo $total_problems ? 'PROBLEMS' : '' ?>'><?php echo $total_problems ?></td>
								<td id="hostTotalsTOTAL" class='hostTotals'><?php echo $total_hosts ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="widget_status_totals_right">
			<table border=0 cellspacing=0 cellpadding=0>
				<tr>
					<td>
						<table border=1 class='serviceTotals'>
							<tr>
							<?php 	foreach ($service_header as $row) { ?>
										<th class="<?php echo $row['th_class'] ?>"><?php echo html::anchor($row['url'], html::specialchars($row['lable']), array('class' => $row['link_class'])) ?></th><?php
									} ?>
							</tr>
							<tr>
								<td id="serviceTotalsOK" class='serviceTotals<?php echo $svc_total_ok ? 'OK' : '' ?>'><?php echo $svc_total_ok ?></td>
								<td id="serviceTotalsWARNING" class='serviceTotals<?php echo $svc_total_warning ? 'WARNING' : '' ?>'><?php echo $svc_total_warning ?></td>
								<td id="serviceTotalsUNKNOWN" class='serviceTotals<?php echo $svc_total_unknown ? 'UNKNOWN' : '' ?>'><?php echo $svc_total_unknown ?></td>
								<td id="serviceTotalsCRITICAL" class='serviceTotals<?php echo $svc_total_critical ? 'CRITICAL' : '' ?>'><?php echo $svc_total_critical ?></td>
								<td id="serviceTotalsPENDING" class='serviceTotals<?php echo $svc_total_pending ? 'PENDING' : '' ?>'><?php echo $svc_total_pending ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align=center>
						<table border=1 class='serviceTotals'>
							<tr>
								<th class='serviceTotals'>
									<?php echo html::anchor('status/service/'.$host.'/'.$host_state.'/28', html::specialchars($label_all_problems)) ?>
								</th>
								<th class='serviceTotals'>
									<?php echo html::anchor('status/service/'.$host.'/'.$host_state.'/all', html::specialchars($label_all_types)) ?>
								</th>
							</tr>
							<tr>
								<td id="serviceTotalsPROBLEMS" class='serviceTotals<?php echo $svc_total_problems ? 'PROBLEMS' : '' ?>'><?php echo $svc_total_problems ?></td>
								<td id="serviceTotalsTOTAL" class='serviceTotals'><?php echo $svc_total_services ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
