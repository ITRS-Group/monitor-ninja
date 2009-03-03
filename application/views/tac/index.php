<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
	# Testform to try ajax calls
	# not translated
	echo form::open(url::site('tac/ajax_host_lookup'), array('id' => 'test_form', 'name' => 'test_form'));
	echo form::input('host_info')."\n";
	echo form::submit('s1', 'Host lookup')."\n";
	echo form::input(array('type' => 'button', 'value' => 'Clear', 'onclick' => 'clearInput();' ))."\n";
	echo form::close();
?>
<div id="response"></div>

<ul>
<?php foreach ($links as $title => $url): ?>
	<li><?php echo html::anchor($url, html::specialchars($title)) ?></li>
<?php endforeach ?>
</ul>

	<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
	?>
