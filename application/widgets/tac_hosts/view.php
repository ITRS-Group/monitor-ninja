<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm" id="widget-network_health">
	<div class="widget-header">
		<strong>Hosts</strong>
	</div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table class='tac' width=516 cellspacing=0 cellpadding=0 border=1>
			<tr>
			<?php	foreach ($header_links as $url => $title) { ?>
				<td class='hostHeader' width=125><?php echo html::anchor($url, html::specialchars($title)) ?></td>
			<?php	} ?>
			</tr>
			<tr>
				<td valign=top>
					<table border=0 width=125 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=bottom width=25>&nbsp;</td>
							<Td width=10>&nbsp;</td>
							<Td valign=top width=100%>
								<table border=0 width=100%>
									<tr>
									<?php 	foreach ($hosts_down as $url => $title) { ?>
										<td class='hostUnimportantProblem' width=125><?php echo html::anchor($url, html::specialchars($title)) ?></td>
									<?php	} ?>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border=0 width=125 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=bottom width=25>&nbsp;</td>
							<Td width=10>&nbsp;</td>
							<Td valign=top width=100%>
								<table border=0 width=100%>
									<tr>
									<?php 	foreach ($hosts_unreachable as $url => $title) { ?>
										<td class='hostUnimportantProblem' width=125><?php echo html::anchor($url, html::specialchars($title)) ?></td>
									<?php	} ?>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border=0 width=125 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=bottom width=25>&nbsp;</td>
							<Td width=10>&nbsp;</td>

							<Td valign=top width=100%>
								<table border=0 width=100%>
									<tr>
									<?php 	foreach ($hosts_up_disabled as $url => $title) { ?>
										<td class='hostUnimportantProblem' width=125><?php echo html::anchor($url, html::specialchars($title)) ?></td>
									<?php	} ?>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border=0 width=125 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=bottom width=25>&nbsp;&nbsp;&nbsp;</td>
							<Td width=10>&nbsp;</td>
							<Td valign=top width=100%>
								<table border=0 width=100%>
									<tr>
									<?php 	foreach ($hosts_pending_disabled as $url => $title) { ?>
										<td class='hostUnimportantProblem' width=125><?php echo html::anchor($url, html::specialchars($title)) ?></td>
									<?php	} ?>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

	</div>
</div>

