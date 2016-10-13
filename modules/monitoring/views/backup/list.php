<?php defined('SYSPATH') OR die('No direct access allowed.');
	if (isset($error) && $error) {
		echo '<div class="alert error">' . $error . '</div>';
	} else {
?>
<table id="backups" class="padd-table">
	<thead>
	<tr>
		<th style="width: 96px"><?php echo _('Actions'); ?></th>
		<th><?php echo _('Backups'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$i = 0;
		$baseurl = url::base() . 'index.php/';
		foreach ($files as $file) {
			$i++;
		?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
			<td>
				<a class="view_backup" href="<?php echo $baseurl . 'backup/view/' . $file; ?>" style="border: 0px" title="View Backup"><span class="icon-16 x16-backup-view"></span></a>
				<a class="restore_backup" href="<?php echo $baseurl . 'backup/restore/' . $file; ?>" style="border: 0px" title="Restore Backup"><span class="icon-16 x16-backup-restore"></span></a>
				<a class="delete_backup" href="<?php echo $baseurl . 'backup/delete/' . $file; ?>" style="border: 0px" title="Delete Backup"><span class="icon-16 x16-backup-delete"></span></a>
			</td>
			<td><a class="download_backup" href="<?php echo $baseurl . 'backup/download/' . $file; ?>" target="_blank" title="Download backup"><?php echo $file; ?></a></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php } ?>
