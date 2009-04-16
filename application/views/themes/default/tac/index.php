<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
?>

<!--<ul>
<?php foreach ($links as $title => $url): ?>
	<li><?php echo html::anchor($url, html::specialchars($title)) ?></li>
<?php endforeach ?>
</ul>--->
