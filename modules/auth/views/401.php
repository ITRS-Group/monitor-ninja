<?php defined('SYSPATH') or die('No direct access allowed.'); ?>
<div class="alert error" >
<?php
echo '<h1><b>'._("I'm sorry, you don't have permission to view this page ...").'</b></h1>';
if (count($messages) > 0) {
	echo '<p>' . array_shift($messages) . "</p>";
}
else {
	echo "<p>And I don't know why! This could be a bug, and we'd really appreciate it if you'd <a target=\"_blank\" href=\"" . Kohana::config('product.support_url', true) . '">contact support</a>.</p>';
	echo "<p>Action was: <b>" . $action . "</b>, please include this information in your support ticket.</p>";
}

if (count($messages) > 0) {
	echo 'In addition';
	foreach ($messages as $message) {
		echo ',<p>' . $message; '</p>';
	}
}
?>
</div>
