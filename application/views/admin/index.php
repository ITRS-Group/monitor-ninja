<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php echo $info ?>

<ul>
<?php foreach ($links as $title => $url): ?>
	<li><?php echo html::anchor($url, html::specialchars($title)) ?></li>
<?php endforeach ?>
</ul>