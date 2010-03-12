<span class="<?php echo $status ? 'ok' : 'error'; ?>"><?php echo $message;
if (!$status) { ?></span> &nbsp;&nbsp;&nbsp; <a href="#" onclick="backup(); return false;">Backup anyway</a><?php } ?>
<span><a id="backup" href="<?php echo url::base() . 'index.php/backup/backup/'; ?>" class="hidden">Backup now</a></span>
