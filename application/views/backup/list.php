<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<script type="text/javascript">
function backup(){
	$('#backupstatus').css( "display", "block" ).load('<?php echo url::base() . 'index.php/backup/backup/'; ?>', function(){
		if ($(this).find('span').hasClass('ok'))
		{
			var file = $('#backupfilename').text();
			if ($('#backups tbody tr:first a:first').text() != file && !$('.download:contains('+file+')').length) {
				$('#backups tbody tr:nth-child(2)').before('<tr class="' + ($('#backups tr:last').attr('class') == 'odd' ? 'even' : 'odd') + '">'
					+ '<td><a class="view" href="<?php echo url::base(); ?>index.php/backup/view/' + file
					+ '" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-view.png'),
						array('alt' => _('View'), 'title' => _('View'))); ?></a>'
					+ ' <a class="restore" href="<?php echo url::base(); ?>index.php/backup/restore/' + file
					+ '" style="border: 0px" title="index.php/backup/restore/'+file+'"><?php echo html::image($this->add_path('/icons/16x16/backup-restore.png'),
						array('alt' => _('Restore'), 'title' => _('Restore'))); ?></a>'
					+ ' <a class="delete" href="<?php echo url::base(); ?>index.php/backup/delete/' + file
					+ '" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-delete.png'),
						array('alt' => _('Delete'), 'title' => _('Delete'))); ?></a>'
					+ '</td>' + '<td><a class="download" href="<?php echo url::base(); ?>index.php/backup/download/' + file + '">' + file + '</a></td></tr>');
			}
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
		'height': 70,
		'onStart': function(link) {
			var l = $(link);
			if(l.is('.restore') && !confirm('Do you really want to restore the backup ' + l.closest('tr').find('.download').text() + ' ?')) {
				l.data('cancelled', true);
				return false;
			}
		}
	});
});

$('#verify').live('click', function(){
	if(status) {
		alert("Already performing an action ("+status+"), try again soon");
		return false;
	}

	var link = $(this);
	status = 'saving';
	$('#backupstatus').css( "display", "block" ).load($(link).attr('href'), function(){
		status = '';
		if ($(this).find('span').hasClass('ok'))
			setTimeout(backup, 2000);
	});
	return false;
});

$('a.restore').live('click', function(ev){
	if(status) {
		alert("Already performing an action ("+status+"), try again soon");
		return false;
	}
	var link = $(this);
	if(link.data('cancelled')) {
		link.removeData('cancelled');
		return false;
	}

	$('#backupstatus').css( "display", "block" ).text('Restoring backup...');
	status = 'restoring';
	$('#fancybox-content').load(link.attr('title'), function() {
		$('#fancybox-close').show();
		status = '';
		$('#backupstatus').text($('#fancybox-content').text());
	});

	return false;
});

$('a.delete').live('click', function(){
	var link = $(this);
	if (confirm('Do you really want to delete ' + $(link).closest('tr').find('.download').text() + ' ?'))
		$('#backupstatus').css( "display", "block" ).load($(link).attr('href'), function(){
			if ($(this).find('span').hasClass('ok'))
				$(link).closest('tr').remove();
		});
	return false;
});

window.onbeforeunload = function(event){
	event = event || window.event;
	var message;
	if(status == 'restoring'){
		message = "Your configuration is being restored from a backup, do you really want to abort?";
	} else if(status == 'saving') {
		message = "Your configuration is being saved, do you really want to abort?";
	}
	if(message) {
		event.returnValue = message;
		return message;
	}
}
var status = '';
</script>

<div>

	<div class="alert warning" style="display: none" id="backupstatus">&nbsp;</div>

	<div style="display: none">
		<div id="restore-status" class="alert warning"><img src="/ninja/application/media/images/loading.gif" /></div>
	</div>

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
			foreach ($files as $file): $i++;
		?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even'; ?>">
			<td>
				<a class="view" href="<?php echo url::base() . 'index.php/backup/view/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-view.png'), array('alt' => _('View'), 'title' => _('View'))); ?></a>
				<a class="restore" href="#restore-status" title="<?php echo url::base() . 'index.php/backup/restore/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-restore.png'), array('alt' => _('Restore'), 'title' => _('Restore'))); ?></a>
				<a class="delete" href="<?php echo url::base() . 'index.php/backup/delete/' . $file; ?>" style="border: 0px"><?php echo html::image($this->add_path('/icons/16x16/backup-delete.png'), array('alt' => _('Delete'), 'title' => _('Delete'))); ?></a>
			</td>
			<td><a class="download" href="<?php echo url::base() . 'index.php/backup/download/' . $file; ?>" target="_blank"><?php echo $file; ?></a></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
