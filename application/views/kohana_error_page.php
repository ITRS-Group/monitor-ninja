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

include('themes/default/menu/menu.php');
$links = $menu_base;

include_once('themes/default/template.php');
