<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html>

<html lang="en">
	<?php

		$data = array(
			'current_skin' => 'default/',
			'keycommands_active' => false
		);

		if (isset($js_strings)) $data['js_strings'] = $js_strings;
		if (isset($base_href)) $data['base_href'] = $base_href;
		if (isset($inline_js)) $data['inline_js'] = $inline_js;

		if (isset($title)) $data['title'] = $title;
		if (isset($css)) $data['css'] = $css;


		if (isset($js) && is_array($js)) {

			$mangled_js = array();
			foreach($js as $orig_js) {
				$delim = (strpos($orig_js, '?') === false) ? '?' : '&';
				$mangled_js[] = $orig_js . $delim . 'request_context=external_widget';
			}

			$data['js'] = $mangled_js;

		}

		View::factory('template_head', $data)->render(true);

	?>

	</head>
	<body>
		<script type="text/javascript">
		$.ajaxSetup({
			'data': {
				'request_context': 'external_widget'
			}
		});
		</script>
		<?php echo $widget->render('index', false); ?>
	</body>
</html>
