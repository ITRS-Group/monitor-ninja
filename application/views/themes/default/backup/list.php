<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<script type="text/javascript">
$('#verify').live('click', function(){
	var link = $(this);
	$('#backupstatus').load($(link).attr('href'), function(){
		if ($(this).find('span').hasClass('ok'))
			$('#backupstatus').load($('#backup').attr('href'), function(){
				if ($(this).find('span').hasClass('ok'))
				{
					var file = $('#backupfilename').text();
					if ($('#backups tr:last td:first').text() != file)
						$('#backups tr:last').after('<tr>'
							+ '<td><a href="<?php echo url::base(); ?>index.php/backup/view/' + file + '">' + file + '</a></td>'
							+ '<td><a class="restore" href="<?php echo url::base(); ?>index.php/backup/restore/' + file + '">restore</a></td>'
							+ '<td><a class="delete" href="<?php echo url::base(); ?>index.php/backup/delete/' + file + '">delete</a></td>'
							+ '</tr>');
				}
			});
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
	if (confirm('Do you really want to delete ' + $(link).closest('td').siblings().eq(0).text() + ' ?'))
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
	<br />
	<p><a id="verify" href="<?php echo url::base() . 'index.php/backup/verify/'; ?>">Save your perfect configuration</a></p>
	<br />
	<table id="backups" class="white-table">
		<?php foreach ($files as $file): ?>
		<tr>
		  <td><a href="<?php echo url::base() . 'index.php/backup/view/' . $file; ?>"><?php echo $file; ?></a></td>
		  <td><a class="restore" href="<?php echo url::base() . 'index.php/backup/restore/' . $file; ?>">restore</a></td>
		  <td><a class="delete" href="<?php echo url::base() . 'index.php/backup/delete/' . $file; ?>">delete</a></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
