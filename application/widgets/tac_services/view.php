<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget movable collapsable removable closeconfirm" id="widget-tac_services">
	<div class="widget-header">
		<strong>Services</strong>
	</div>
	<div class="widget-editbox">
		<!--Edit the widget here-->
	</div>
	<div class="widget-content">
		<table class='tac' width=641 cellspacing=0 cellpadding=0 border=1>
			<tr>
			<?php	foreach ($header_links as $url => $title) { ?>
				<td class='hostHeader' width=125><?php echo html::anchor($url, html::specialchars($title)) ?></td>
			<?php	} ?>
			</tr>
			<tr>
				<td valign=top>
					<table border=0 width=125 cellspacing=0 cellpadding=0>
						<tr>
							<td valign=bottom width=25>&nbsp;&nbsp;&nbsp;</td>
							<Td width=10>&nbsp;</td>
							<Td valign=top width=100%>
								<table border=0 width=100%>
									<tr>
									<?php 	foreach ($services_critical as $url => $title) { ?>
										<td width=100% class='serviceImportantProblem'><?php echo html::anchor($url, html::specialchars($title)) ?></td>
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
									<?php 	foreach ($services_warning as $url => $title) { ?>
										<td width=100% class='serviceImportantProblem'><?php echo html::anchor($url, html::specialchars($title)) ?></td>
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
									<?php 	foreach ($services_unknown as $url => $title) { ?>
										<td width=100% class='serviceImportantProblem'><?php echo html::anchor($url, html::specialchars($title)) ?></td>
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
									<?php 	foreach ($services_ok_disabled as $url => $title) { ?>
										<td width=100% class='serviceUnimportantProblem'><?php echo html::anchor($url, html::specialchars($title)) ?></td>
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
							<td valign=top width=100%>
								<table border=0 width=100%>
									<tr>
									<?php 	foreach ($services_pending_disabled as $url => $title) { ?>
										<td width=100% class='serviceUnimportantProblem'><?php echo html::anchor($url, html::specialchars($title)) ?></td>
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

