<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<div class="widget-place col">
<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
?>
</div>

<!--<ul>
<?php foreach ($links as $title => $url): ?>
	<li><?php echo html::anchor($url, html::specialchars($title)) ?></li>
<?php endforeach ?>
</ul>-->
