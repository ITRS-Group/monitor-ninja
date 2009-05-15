<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php if (isset($title)) echo html::specialchars($title) ?></title>
		<?php echo html::stylesheet('application/views/themes/default/css/common.css') ?>
		<?php echo html::stylesheet('application/views/themes/default/css/css-buttons.css') ?>
		<script type="text/javascript">
			//<!--
				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
			//-->
		</script>
		<?php
			if (!empty($js_header)) {
				echo $js_header;
			}
		?>
	</head>

	<body>
		
		
			<div id="login-table">
			<?php if (isset($error_msg)) echo $error_msg; ?>
			<?php echo form::open('default/do_login'); ?>
			<table>
				<tr>
					<td colspan="2"><h3><?php echo $this->translate->_('Login to NINJA') ?></h3><hr /></td>
				</tr>
				<tr>
					<td><?php echo $username ?></td>
					<td><cite><em></em><?php echo form::input('username','','class="text"') ?><em></em></cite></td>
				</tr>
				<tr>
					<td><?php echo $password ?></td>
					<td><cite><em></em><?php echo form::password('password','','class="text"') ?><em></em></cite></td>
				</tr>
				<tr>
				<tr><td colspan="2"><hr /></td></tr>
				<td>&nbsp;</td>
				<td>
					<?php
						echo csrf::form_field();
						echo form::submit('login', $login_btn_txt, 'class="bn"');
					?>
				</td>
				</tr>
			</table>
		<?php echo form::close() ?>
			</div>
		
	</body>
</html>