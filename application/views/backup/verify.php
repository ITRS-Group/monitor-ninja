<span class="<?php echo $status ? 'ok' : 'error'; ?>"><?php echo $message; ?></span>
<?php if (!$status): ?> &nbsp;&nbsp;&nbsp; <a href="#" onclick="backup(); return false;">Backup anyway</a><?php endif; ?>
