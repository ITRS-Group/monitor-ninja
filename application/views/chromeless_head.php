<head>
<?php
	if (!empty($base_href)) {
		echo (!empty($base_href)) ? '<base href="'.$base_href.'" />' : '';
	}
?>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">

	<title>
		<?php
			echo (isset($title)) ? Kohana::config('config.product_name').' » '.html::specialchars($title) : Kohana::config('config.product_name');
		?>
	</title>

	<?php
		echo html::link('application/views/icons/favicon.ico','icon','image/x-icon');
		echo html::link('application/media/css/lib.popover.css', 'stylesheet', 'text/css', false, 'screen');
		echo html::link('application/media/css/lib.notify.css', 'stylesheet', 'text/css', false, 'screen');
	?>

	<link href="<?php echo ninja::add_path('css/layout.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/form.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/icons.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/'.$current_skin.'common.css'); ?>" type="text/css" rel="stylesheet" media="all" />
	<link href="<?php echo ninja::add_path('css/'.$current_skin.'print.css'); ?>" type="text/css" rel="stylesheet" media="print" />
	<link type="text/css" rel="stylesheet" href="<?php echo ninja::add_path('css/'.$current_skin.'jquery-ui-custom.css') ?>" media="screen" />
	<?php
		$v = new View('css_header', array('css' => isset($css)?$css:array()));
		$v->render(true);
	?>
	<script type="text/javascript">
		/* Hack for lack of console.log() in ie7 */
		if (!window.console) console = {log: function() {}, error: function() {}, dir: function() {}};
	</script>


	<?php
		$v = new View('js_header', array('js' => isset($js)?$js:array()));
		$v->render(true);
	?>

</head>
