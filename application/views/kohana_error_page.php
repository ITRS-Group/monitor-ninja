<?php defined('SYSPATH') OR die('No direct access allowed.');

$table_crashed = function($error_string) {
	if(preg_match("~Table '([^']+)' is marked as crashed and should be repaired~", $error_string, $match)) {
		return $match[1];
	}
	return null;
};

$content = '<div id="framework_error" style="width:42em;margin:0px auto;">';
$content .= '<h3>'.html::specialchars($error).'</h3>';
$content .= '<p>'.html::specialchars($description).'</p>';
$crash_info = null;
if($table = $table_crashed($message)) {
	$content .= $crash_info = "<p>The table $table is marked as crashed: login as root on the Monitor server and run</p><pre>mysqlcheck --repair --databases merlin</pre><p>There is more information in the <a href='https://dev.mysql.com/doc/refman/5.0/en/rebuilding-tables.html'>MySQL manual</a>.</p>";
}

if ( ! empty($line) AND ! empty($file)) {
	$content .= '<p>'.Kohana::lang('core.error_file_line', $file, $line).'</p>';
}
$content .= '<p><code class="block">'.$message.'<code></p>';
if ( ! empty($trace)){
	$content .= '<h3>'.Kohana::lang('core.stack_trace').'</h3>';
	$content .= $trace;
}
$content .= '<p class="stats">'.Kohana::lang('core.stats_footer').'</p>';
foreach(Kohana::config('exception.shell_commands') as $command) {
	exec($command, $output, $exit_value);
	$content .= "<p class='stats'>$command (exit code $exit_value):<br />".implode('<br />', $output).'</p>';
}
foreach(Kohana::config('exception.extra_info') as $header => $info) {
	$content .= "<p class='stats'>$header: $info</p>";
}
$content .= '</div>';

$content .= '<style type="text/css">'.file_get_contents(Kohana::find_file('views', 'kohana_errors', FALSE, 'css')).'</style>';
$js = array();
$css = array();

if (IN_PRODUCTION) {
	$tmp_dir = Kohana::Config('exception.tmp_dir') ? Kohana::Config('exception.tmp_dir') : '/tmp/ninja-stacktraces/';
	$tmp_dir_perm = Kohana::Config('exception.tmp_dir_perm') ? Kohana::Config('exception.tmp_dir_perm') : 0700;
	@mkdir($tmp_dir, $tmp_dir_perm, true);
	$file = tempnam($tmp_dir, date('Ymd-hi').'-');
	$fd = fopen($file, 'w');
	$error_data = "<html><body>$content</body></html>";
	$writeerror = false;
	fwrite($fd, $error_data) or $writeerror = true;

	fclose($fd);

	$content = '<div><h3>There was an error rendering the page</h3>';
	if (!$writeerror) {
		$content .= '<p>Please contact your administrator.<br />The debug information in '.$file.' will be essential to troubleshooting the problem, so please include it if you file a bug report or contact op5 Support.</p></div>';
	} else {
		// by special casing this here once, we save some support time every time
		// log data clobbers a customers hard drive
		$content .= "<p>Additionally, there was an error when trying to save the debug information to a file in '$tmp_dir'. Please make sure that your hard drive isn't full.</p></div>";
	}
	$content .= $crash_info;
	unset($tmp_dir);
}
$title = 'Error';
require('menu/menu.php');
$links = $menu_base;

if ($_SERVER['REQUEST_METHOD'] == 'POST')
	$disable_refresh = true;

$current_skin = 'default/';
require('template.php');
