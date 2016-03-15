<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="overlay">
	<div class="dialog center">
		<p>
		<?php echo brand::get(); ?><br />
		</p>
		<h2><?php echo _('Service unavailable'); ?></h2>
		<p><?php echo _('We were unable to satisfy your request at this time, you may attempt to refresh this page in your browser'); ?></p>
		<p><?php echo _('Please contact your administrator.'); ?></p>
		<hr>
		<h2>Troubleshooting information</h2>
<?php if (isset($exception)) { ?>
		<code>
			<?php echo $exception->getMessage(); ?>
		</code>
<?php } ?>
	</div>
</div>
