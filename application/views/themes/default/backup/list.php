<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98">
<?php
if (isset($error_msg)) echo $error_msg; ?>
<?php echo $status_msg ?>
	<h2><?php echo $this->translate->_('Backup/Restore'); ?></h2>
	<p><a href="#">Save your perfect configuration</a></p>
	<br />
	<table class="white-table">
		<?php foreach ($files as $file):
			if (substr($file, -7) == '.tar.gz'):
		?>
		<tr>
		  <td><?php echo substr($file, 0, -7); ?></td>
		  <td><a href="#">view</a></td>
		  <td><a href="#">restore</a></td>
		  <td><a href="#">delete</a></td>
		</tr>
		<?php endif; endforeach; ?>
	</table>
</div>
