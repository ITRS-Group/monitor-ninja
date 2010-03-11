<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98">
<?php
if (isset($error_msg)) echo $error_msg; ?>
<?php echo $status_msg ?>
	<h2><?php echo $this->translate->_('Backup/Restore'); ?></h2>
	<p><a href="#">Save your perfect configuration</a></p>
	<br />
	<table class="white-table">
		<?php foreach ($files as $file): ?>
		<tr>
		  <td><a href="<?php echo url::base() . 'index.php/backup/view/' . $file; ?>"><?php echo $file; ?></a></td>
		  <td><a href="#">restore</a></td>
		  <td><a href="#">delete</a></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
