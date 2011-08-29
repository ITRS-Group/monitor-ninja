<?php defined('SYSPATH') OR die('No direct access allowed.');

$content = '<div id="framework_error" style="width:42em;margin:0px auto;">';
$content .= '<h3>'.html::specialchars($error).'</h3>';
$content .= '<p>'.html::specialchars($description).'</p>';
if ( ! empty($line) AND ! empty($file)) {
	$content .= '<p>'.Kohana::lang('core.error_file_line', $file, $line).'</p>';
}
$content .= '<p><code class="block">'.$message.'<code></p>';
if ( ! empty($trace)){
	$content .= '<h3>'.Kohana::lang('core.stack_trace').'</h3>';
	$content .= $trace;
}
$content .= '<p class="stats">'.Kohana::lang('core.stats_footer').'</p>';
$content .= '</div>';

$css_header = '<style type="text/css">'.file_get_contents(Kohana::find_file('views', 'kohana_errors', FALSE, 'css')).'</style>';

if (Authenticated_Controller::ALLOW_PRODUCTION === true) {
	mkdir('/tmp/ninja-stacktraces/', 0700, true);
	$file = tempnam('/tmp/ninja-stacktraces', date('Ymd-hi').'-');
	$fd = fopen($file, 'w');
	$error_data = "<html><head>$css_header</head><body>$content</body></html>";
	$writeerror = false;
	fwrite($fd, $error_data) or $writeerror = true;

	fclose($fd);

	$css_header = false;
	$content = '<h3>There was an error rendering the page</h3>';
	if (!$writeerror) {
		$content .= '<p>Please contact your administrator. Debug information has been saved to "'.$file.'".</p>';
	} else {
		// by special casing this here once, we save some support time every time
		// log data clobbers a customers hard drive
		$content .= "<p>Additionally, there was an error when trying to save the debug information to \"$file\". Please check that your hard drive isn't full.</p>";
	}
}
include('themes/default/menu/menu.php');
$links = $menu_base;

include_once('themes/default/template.php');
