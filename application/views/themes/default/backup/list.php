<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98">
<?php
if (isset($error_msg)) echo $error_msg; ?>
<?php echo $status_msg ?>
	<h2><?php echo $this->translate->_('Backup/Restore'); ?></h2>
	<p><a href="#">Save your perfect configuration</a></p>
	<br />
	<table class="white-table">
		<tr>
		  <td>backup-2010-03-10_16.30</td>
		  <td><a href="#">view</a></td>
		  <td><a href="#">restore</a></td>
		  <td><a href="#">delete</a></td>
		</tr>
		<tr>
		  <td>backup-2010-03-10_16.24</td>
		  <td><a href="#">view</a></td>
		  <td><a href="#">restore</a></td>
		  <td><a href="#">delete</a></td>
		</tr>
		<tr>
		  <td>backup-2010-03-10_16.10</td>
		  <td><a href="#">view</a></td>
		  <td><a href="#">restore</a></td>
		  <td><a href="#">delete</a></td>
		</tr>
	</table>
</div>
