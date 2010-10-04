<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo Kohana::config('config.product_name').' '.$this->translate->_('login'); ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->add_template_path('css/default/common.css') ?>" />
		<?php echo html::link($this->add_path('icons/16x16/favicon.ico'),'icon','image/icon') ?>
		<?php echo html::script('application/media/js/jquery.min.js'); ?>
		<script type="text/javascript">
			//<!--
				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
				$(document).ready(function() {
					$('#login_form').bind('submit', function() {
						$('#loading').show();
						$('#login').attr('disabled', true);
						$('#login').attr('value', '<?php echo $this->translate->_('Please wait...') ?>');
					});
				});
			//-->
		</script>
		<?php echo (!empty($js_header)) ? $js_header : '' ?>
	</head>

	<body>
		<div id="login-table">
			<?php if (isset($error_msg)) echo $error_msg; ?>
			<?php echo form::open('default/do_login', array('id' => 'login_form')); ?>
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
						?><br /><br />
						<div id="loading" style="display:none;">
							<?php echo html::image('application/media/images/loading.gif') ?>
						</div>
					</td>
				</tr>
			</table>
		<?php echo form::close() ?>
		</div>
	</body>
</html>