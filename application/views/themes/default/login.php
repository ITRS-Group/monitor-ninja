<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php if (isset($title)) echo html::specialchars($title) ?></title>
		<link type="text/css" rel="stylesheet" href="/ninja/application/views/themes/default/css/default/common.css.php" />
		<?php echo html::link('application/views/themes/default/icons/16x16/favicon.ico','icon','image/icon') ?>
		<script type="text/javascript">
			//<!--
				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
			//-->
		</script>
		<?php echo (!empty($js_header)) ? $js_header : '' ?>
	</head>

	<body>
		<div id="login-table">
			<?php if (isset($error_msg)) echo $error_msg; ?>
			<?php echo form::open('default/do_login'); ?>
			<table border="1">
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<td><?php echo $username ?></td>
					<td><?php echo form::input('username','','class="i160"') ?></td>
				</tr>
				<tr>
					<td><?php echo $password ?></td>
					<td><?php echo form::password('password','','class="i160"') ?></td>
				</tr>
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<?php
							echo csrf::form_field();
							echo form::submit('login', $login_btn_txt, 'style="margin-left: 5px"');
						?>
					</td>
				</tr>
			</table>
		<?php echo form::close() ?>
		</div>
	</body>
</html>