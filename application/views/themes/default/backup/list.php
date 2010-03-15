<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<script type="text/javascript">
function backup(){
	$('#backupstatus').load($('#backup').attr('href'), function(){
		if ($(this).find('span').hasClass('ok'))
		{
			var file = $('#backupfilename').text();
			if ($('#backups tr:last a:first').text() != file)
				$('#backups tr:last').after('<tr>'
					+ '<td><a class="download" href="/backup/' + file + '<?php echo $suffix; ?>">' + file + '</a></td>'
					+ '<td><a class="view" href="<?php echo url::base(); ?>index.php/backup/view/' + file + '">view</a></td>'
					+ '<td><a class="restore" href="<?php echo url::base(); ?>index.php/backup/restore/' + file + '">restore</a></td>'
					+ '<td><a class="delete" href="<?php echo url::base(); ?>index.php/backup/delete/' + file + '">delete</a></td>'
					+ '</tr>');
		}
	});
}
$('#verify').live('click', function(){
	var link = $(this);
	$('#backupstatus').load($(link).attr('href'), function(){
		if ($(this).find('span').hasClass('ok'))
			setTimeout(backup, 2000);
	});
	return false;
});
$('a.restore').live('click', function(){
	var link = $(this);
	$('#backupstatus').load($(link).attr('href'));
	return false;
});
$('a.delete').live('click', function(){
	var link = $(this);
	if (confirm('Do you really want to delete ' + $(link).closest('tr').find('.download').text() + ' ?'))
		$('#backupstatus').load($(link).attr('href'), function(){
			if ($(this).find('span').hasClass('ok'))
				$(link).closest('tr').remove();
		});
	return false;
});
</script>

<div class="widget left w98">
	<h2><?php echo $this->translate->_('Backup/Restore'); ?></h2>
	<div id="backupstatus">&nbsp;</div>
	<p><a id="verify" href="<?php echo url::base() . 'index.php/backup/verify/'; ?>">Save your perfect configuration</a></p>
	<br />
	<table id="backups">
		<tr>
			<th class="headerNone"><?php echo $this->translate->_('Backups'); ?></th>
			<th class="headerNone" style="width: 50px"><?php echo $this->translate->_('Actions'); ?></th>
		</tr>
		<?php $i = 0; foreach ($files as $file): $i++;?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
		  <td><a class="download" href="/backup/<?php echo $file . $suffix; ?>"><?php echo $file; ?></a></td>
		  <td>
				<a class="view" href="<?php echo url::base() . 'index.php/backup/view/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-view.png'), array('alt' => $this->translate->_('View'), 'title' => $this->translate->_('View')));?></a>
			<a class="restore" href="<?php echo url::base() . 'index.php/backup/restore/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-restore.png'), array('alt' => $this->translate->_('Restore'), 'title' => $this->translate->_('Restore')));?></a>
			<a class="delete" href="<?php echo url::base() . 'index.php/backup/delete/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-delete.png'), array('alt' => $this->translate->_('Delete'), 'title' => $this->translate->_('Delete')));?></a>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
