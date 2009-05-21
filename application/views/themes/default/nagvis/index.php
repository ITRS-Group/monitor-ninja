<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div style="margin-left: 20px;">
	<div style="float: right; width: 200px;">
		<ul>
			<li><a href="<?php echo Kohana::config('config.nagvis_path'); ?>nagvis/index.php" target="nagvis">Index</a></li>
			<?php
			foreach ($maps as $map)
				echo '<li><a href="'.Kohana::config('config.nagvis_path').'nagvis/index.php?map='.$map.'" target="nagvis">'.$map.'</a></li>';
			?>
			<li><a href="<?php echo Kohana::config('config.nagvis_path'); ?>nagvis/index.php?automap=1" target="nagvis">Automap</a></li>
		</ul>
	</div>
	<iframe name="nagvis" src="<?php echo Kohana::config('config.nagvis_path'); ?>" width="900" height="700" frameborder="no">
		Could not load NagVis!
	</iframe>
</div>
