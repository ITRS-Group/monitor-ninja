<div class="header" id="header">
	<div class="supermenu">
		<ul>

			<!-- Classes are used by javascript navigation -->

			<li class="supermenu-button" id="about-button" title="About">
				<span class="icon-24 x24-info"></span>
			</li>
			<li class="supermenu-button" id="monitoring-button" title="Monitoring">
				<span class="icon-24 x24-link"></span>
			</li>
			<li class="supermenu-button" id="reporting-button" title="Reporting">
				<span class="icon-24 x24-news"></span>
			</li>
			<li class="supermenu-button" id="configuration-button" title="Configuration">
				<span class="icon-24 x24-settings"></span>
			</li>

		</ul>
	</div>
	<?php echo html::image('application/views/themes/default/icons/op5.gif', array('width' => '80', 'style' => 'float: left; margin: 10px 0 0 20px;')); ?>
	<div class="headercontent">

		Welcome monitor, <a href="#">Log out</a><br />
		<input class="header-search" type="text" style="height: 10px;" value="Search" />
		<input type="submit" value="Search" /><br />
		<a href="widgets.php" class="image-link">
			<span class="icon-16 x16-refresh"></span>
		</a>
		<a href="index.php" class="image-link">
			<span class="icon-16 x16-versioninfo"></span>
		</a>
		<a href="widgets.php" class="image-link">
			<span class="icon-16 x16-settings"></span>
		</a>
		<a href="<?php echo '//'.$_SERVER['HTTP_HOST'].'/ninja/dojo/index.html'; ?>" class="header-action">
			<span class="icon-16 x16-status-detail"></span>
		</a>
	</div>
</div>
