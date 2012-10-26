<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<script type="text/javascript">
function backup(){
	$('#backupstatus').load($('#backup').attr('href'), function(){
		if ($(this).find('span').hasClass('ok'))
		{
			var file = $('#backupfilename').text();
			if ($('#backups tbody tr:first a:first').text() != file)
				$('#backups tbody tr:first').before('<tr class="' + ($('#backups tr:last').attr('class') == 'odd' ? 'even' : 'odd') + '">'
					+ '<td><a class="download" href="<?php echo url::base(); ?>index.php/backup/download/' + file + '">' + file + '</a></td>'
					+ '<td><a class="view" href="<?php echo url::base(); ?>index.php/backup/view/' + file
					+ '" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-view.png'),
						array('alt' => _('View'), 'title' => _('View'))); ?></a>'
					+ ' <a class="restore" href="<?php echo url::base(); ?>index.php/backup/restore/' + file
					+ '" style="border: 0px" title="index.php/backup/restore/'+file+'"><?php echo html::image($this->add_path('/icons/16x16/backup-restore.png'),
						array('alt' => _('Restore'), 'title' => _('Restore'))); ?></a>'
					+ ' <a class="delete" href="<?php echo url::base(); ?>index.php/backup/delete/' + file
					+ '" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-delete.png'),
						array('alt' => _('Delete'), 'title' => _('Delete'))); ?></a>'
					+ '</td></tr>');
		}
	});
}

$(document).ready(function() {
	$('a.restore').fancybox({
			'overlayOpacity': 0.7,
			'overlayColor' : '#ffffff',
			'hideOnContentClick': false,
			'hideOnOverlayClick': false,
			'titleShow': false,
			'showCloseButton': false,
			'enableEscapeButton': false,
			'autoDimensions': false,
			'width': 250,
			'height': 70
	});
});
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
	if (confirm('Do you really want to restore the backup ' + $(link).closest('tr').find('.download').text() + ' ?')) {
		$('#backupstatus').text('Restoring backup...');
		status = 'restoring';
		$('#fancybox-content').load($(link).attr('title'), function() {
			$('#fancybox-close').show();
			status = '';
			$('#backupstatus').text($('#fancybox-content').text());
		});
	}
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

window.onbeforeunload = function(event){
	event = event || window.event;
	if(status == 'restoring'){
		return event.returnValue = "A backup is being restored!"
	}
}
var status = '';
</script>

<div	>
	<h2><?php echo _('Backup/Restore'); ?></h2>
	<div id="backupstatus">&nbsp;</div>
	<div style="display: none">
		<div id="restore-status"><img src="/ninja/application/media/images/loading.gif" /></div>
	</div>
	<p><a id="verify" href="<?php echo url::base() . 'index.php/backup/verify/'; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup.png'),array('alt' => _('Save your current Monitor configuration'), 'title' => _('Save your current Monitor configuration'), 'style' => 'margin-bottom: -3px')); ?></a>
	<a id="verify" href="<?php echo url::base() . 'index.php/backup/verify/'; ?>"><?php echo _('Save your current op5 Monitor configuration'); ?></a></p>
	<br />
	<table id="backups">
		<thead>
		<tr>
			<th class="headerNone"><?php echo _('Backups'); ?></th>
			<th class="headerNone" style="width: 50px"><?php echo _('Actions'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php $i = 0; foreach ($files as $file): $i++; ?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
			<td><a class="download" href="<?php echo url::base() . 'index.php/backup/download/' . $file; ?>" target="_blank"><?php echo $file; ?></a></td>
			<td>
				<a class="view" href="<?php echo url::base() . 'index.php/backup/view/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-view.png'), array('alt' => _('View'), 'title' => _('View'))); ?></a>
				<a class="restore" href="#restore-status" title="<?php echo url::base() . 'index.php/backup/restore/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-restore.png'), array('alt' => _('Restore'), 'title' => _('Restore'))); ?></a>
				<a class="delete" href="<?php echo url::base() . 'index.php/backup/delete/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-delete.png'), array('alt' => _('Delete'), 'title' => _('Delete'))); ?></a>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
