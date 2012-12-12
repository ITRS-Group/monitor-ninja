<!DOCTYPE html>
<html>
	
	<?php
		include_once('/dojo/head.php');
	?>

	<body>

		<div class="container">
			
			<div class="logo"><img src="icons/op5.gif" style="float: left; margin-left: 15px" /></div>

			<?php
				include_once('/dojo/header.php');
			?>

			<section class="navigation" id="navigation">
				<div class="menu" id="main-menu">

				<?php
					include_once('/dojo/menu.php');
				?>

				</div>
				<div class="slider" id="slider" title="Collapse Navigation">
					<div class="slide-button">
						::
					</div>
				</div>

			</section>

			<section class="content" id="content">
				
				<section class="widget-placeholder">

					<div class="widget">
						<div class="widget-header">
							<div class="widget-label"><h6>Network Outages</h6></div>
							<div class="widget-menu">
								<img src="./icons/12x12/copy.png">
								<img src="./icons/12x12/box-mimimize.png">
								<img src="./icons/12x12/box-config.png">
								<img src="./icons/12x12/box-close.png">
							</div>
							<div class="clear"></div>
						</div>
						<div class="widget-content">
							<table class="w-table">
								<tbody>
									
									<tr>
										<td class="dark">
											<img class="alpha" src="./icons/24x24/shield-not-critical.png" />
										</td>
										<td>
											N/A
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div class="widget">
						<div class="widget-header">
							<div class="widget-label"><h6>Scheduled Downtime</h6></div>
							<div class="widget-menu">
								<img src="./icons/12x12/copy.png">
								<img src="./icons/12x12/box-mimimize.png">
								<img src="./icons/12x12/box-config.png">
								<img src="./icons/12x12/box-close.png">
							</div>
							<div class="clear"></div>
						</div>
						<div class="widget-content">
							<table class="w-table">
								<tbody>
									
									<tr>
										<td class="dark">
											<img class="alpha" src="./icons/24x24/shield-not-critical.png" />
										</td>
										<td>
											N/A
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div class="widget">
						<div class="widget-header">
							<div class="widget-label"><h6>Monitoring Performance</h6></div>
							<div class="widget-menu">
								<img src="./icons/12x12/copy.png">
								<img src="./icons/12x12/box-mimimize.png">
								<img src="./icons/12x12/box-config.png">
								<img src="./icons/12x12/box-close.png">
							</div>
							<div class="clear"></div>
						</div>
						<div class="widget-content">
							<table class="w-table">
								<tbody>
									
									<tr>
										<td class="dark">
											<img src="./icons/20x20/time.png" />
										</td>
										<td>
											Service check execution time: <br />
											-1.00 / -1.00 / 0.0000 sec
										</td>
									</tr>

									<tr>
										<td class="dark">
											<img src="./icons/20x20/time_latency.png" />
										</td>
										<td>
											Service Check Latency: <br />
											-1.00 / -1.00 / 0.000 sec
										</td>
									</tr>

									<tr>
										<td class="dark">
											<img class="alpha" src="./icons/20x20/time.png" />
										</td>
										<td>
											Host Check Execution Time:<br />
											N/A
										</td>
									</tr>

									<tr>
										<td class="dark">
											<img class="alpha" src="./icons/20x20/time_latency.png" />
										</td>
										<td>
											Host Check Latency:<br />
											N/A
										</td>
									</tr>

									<tr>
										<td class="dark">
											<img src="./icons/20x20/share.png" />
										</td>
										<td>
											# Active Host / Service Checks:<br />
											0	/ 0
										</td>
									</tr>

									<tr>
										<td class="dark">
											<img src="./icons/20x20/share2.png" />
										</td>
										<td>
											# Passive Host / Service Checks:<br />
											0	/ 0
										</td>
									</tr>

									

								</tbody>
							</table>
						</div>
					</div>

				</section>

				<section class="widget-placeholder">
					<div class="widget">
						<div class="widget-header">
							<div class="widget-label"><h6>Disabled Checks</h6></div>
							<div class="widget-menu">
								<img src="./icons/12x12/copy.png">
								<img src="./icons/12x12/box-mimimize.png">
								<img src="./icons/12x12/box-config.png">
								<img src="./icons/12x12/box-close.png">
							</div>
							<div class="clear"></div>
						</div>
						<div class="widget-content">
							<table class="w-table">
								<tbody>
									
									<tr>
										<td class="dark">
											<img class="alpha" src="./icons/24x24/shield-not-critical.png" />
										</td>
										<td>
											N/A
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div class="widget">
						<div class="widget-header">
							<div class="widget-label"><h6>Acknowledged Problems</h6></div>
							<div class="widget-menu">
								<img src="./icons/12x12/copy.png">
								<img src="./icons/12x12/box-mimimize.png">
								<img src="./icons/12x12/box-config.png">
								<img src="./icons/12x12/box-close.png">
							</div>
							<div class="clear"></div>
						</div>
						<div class="widget-content">
							<table class="w-table">
								<tbody>
									
									<tr>
										<td class="dark">
											<img class="alpha" src="./icons/24x24/shield-not-critical.png" />
										</td>
										<td>
											N/A
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div class="widget">
						<div class="widget-header">
							<div class="widget-label"><h6>Geomap</h6></div>
							<div class="widget-menu">
								<img src="./icons/12x12/copy.png">
								<img src="./icons/12x12/box-mimimize.png">
								<img src="./icons/12x12/box-config.png">
								<img src="./icons/12x12/box-close.png">
							</div>
							<div class="clear"></div>
						</div>
						<div class="widget-content widget-content-error">
							Could not load widget
						</div>
					</div>
				</section>

				<section class="widget-placeholder">

					<div class="widget">
						<div class="widget-header">
							<div class="widget-label"><h6>Network Health</h6></div>
							<div class="widget-menu">
								<img src="./icons/12x12/copy.png">
								<img src="./icons/12x12/box-mimimize.png">
								<img src="./icons/12x12/box-config.png">
								<img src="./icons/12x12/box-close.png">
							</div>
							<div class="clear"></div>
						</div>
						<div class="widget-content">
							<table class="w-table">
								<tbody>
									
									<tr>
										<td class="dark">
											<img class="alpha" src="./icons/24x24/shield-not-critical.png" />
										</td>
										<td>
											N/A
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

				</section>

			</section>

		</div>
		
		<script type="text/javascript" src="script.js"></script>

	</body>
</html>
