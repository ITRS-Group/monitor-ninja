<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="box">
	<?php echo $info ?>
</div>


<ul>
	<?php foreach ($links as $title => $url): ?>
		<li><?php echo html::anchor($url, html::specialchars($title)) ?></li>
	<?php endforeach ?>
</ul>