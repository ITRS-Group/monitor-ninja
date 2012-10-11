<section class="header" id="header">
	<div class="supermenu">
		<ul>

			<!-- Classes are used by javascript navigation -->

			<li class="supermenu-button" id="about-button" title="About">
				<?php echo html::image('application/views/themes/default/icons/24x24/info.png'); ?>
			</li>
			<li class="supermenu-button" id="monitoring-button" title="Monitoring">
				<?php echo html::image('application/views/themes/default/icons/24x24/link.png'); ?>
			</li>
			<li class="supermenu-button" id="reporting-button" title="Reporting">
				<?php echo html::image('application/views/themes/default/icons/24x24/news.png'); ?>
			</li>
			<li class="supermenu-button" id="configuration-button" title="Configuration">
				<?php echo html::image('application/views/themes/default/icons/24x24/settings.png'); ?>
			</li>

		</ul>
	</div>
	<?php echo html::image('application/views/themes/default/icons/op5.gif', array('width' => '80', 'style' => 'float: left; margin: 10px 0 0 20px;')); ?>
	<div class="headercontent">

		Welcome monitor, <a href="#">Log out</a><br />
		<input class="header-search" type="text" style="height: 10px;" value="Search" />
		<input type="submit" value="Search" /><br />
		<a href="widgets.php" class="image-link">
				<?php echo html::image('application/views/themes/default/icons/16x16/refresh.png', array('class' => 'header-action')); ?>
		</a>
		<a href="index.php" class="image-link">
			<?php echo html::image('application/views/themes/default/icons/16x16/versioninfo.png', array('class' => 'header-action')); ?>
		</a>
		<a href="widgets.php" class="image-link">
			<?php echo html::image('application/views/themes/default/icons/16x16/settings.png', array('class' => 'header-action')); ?>
		</a>
	</div>
</section>