<h1>Command executed</h1>
<?php
if ($count === $success) {
	if ($count === 1) {
?>
	<p>Successfully ran <?php echo $command; ?></p>
<?php
	} else {
?>
	<p>Successfully ran <?php echo $command; ?> for <?php echo $count . ' ' . $table; ?></p>
<?php
	}
} else {
?>
<?php
	if ($success > 0) {
		?>
			<p>Successfully ran <?php echo $command; ?> for <?php echo ($success) . ' ' . $table; ?></p><br />
		<?php
	}
?>
<p>Failed to run <?php echo $command; ?> for <?php echo ($count - $success) . ' ' . $table; ?></p>
<ul>
<?php

	foreach ($results as $item) {
		if (isset($item['result']['status']) && !$item['result']['status']) {
			echo "<li>Failed for <b>" . $item['object'] . "</b> with output: <i>'" . $item['result']['output'] . "'</i></li>";
		}
	}
?>
</ul>
<?php
}
