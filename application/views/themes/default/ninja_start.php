<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="box">
	<p>
		<?php echo $info ?><br />
	</p>
</div>

<ul>
<?php foreach ($links as $title => $url): ?>
	<li><?php echo html::anchor($url, html::specialchars($title)) ?></li>
<?php endforeach ?>
</ul>