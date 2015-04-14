<?php defined('SYSPATH') OR die('No direct access allowed.');

$table_crashed = function($error_string) {
	if(preg_match("~Table '([^']+)' is marked as crashed and should be repaired~", $error_string, $match)) {
		return $match[1];
	}
	return null;
};

$content = '<div id="framework_error">';
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
	$output = null;
	exec($command, $output, $exit_value);
	$content .= "<p class='stats'><b>$command</b> (exit code $exit_value):<br />".implode('<br />', array_map('htmlentities', $output)).'</p>';
}
foreach(Kohana::config('exception.extra_info') as $header => $info) {
	$content .= "<p class='stats'>$header: $info</p>";
}
$content .= '</div>';

$content .= '<style type="text/css">'.file_get_contents(Kohana::find_file('views', 'kohana_errors', FALSE, 'css')).'</style>';
$js = array();
$css = array();

if (IN_PRODUCTION) {
	$tmp_dir = Kohana::Config('exception.tmp_dir') ? Kohana::Config('exception.tmp_dir') : '/tmp/ninja-stacktraces';
	$tmp_dir = rtrim($tmp_dir, "/");
	$tmp_dir_perm = Kohana::Config('exception.tmp_dir_perm') ? Kohana::Config('exception.tmp_dir_perm') : 0700;
	@mkdir($tmp_dir, $tmp_dir_perm, true);

	$error_html = "<!doctype html><html><head><meta charset='UTF-8' /></head><body>$content</body></html>";

	// we can't use tmpnam() because we need a suffix, and we don't have
	// mkstemp() in php; but sha1 is certainly unique enough with a small
	// enough output to fit nicely into a filename
	$filename = $tmp_dir.'/'.date('Ymd-Hi').'-'.sha1($error_html).'.html';
	$write_successful = file_put_contents($filename, $error_html);

	// reset content to display less information, adhere to IN_PRODUCTION
	$content = '<div><h3>There was an error rendering the page</h3>';
	if($write_successful) {
		$content .= '<p>Please contact your administrator.<br />The debug information in '.$filename.' will be essential to troubleshooting the problem, so please include it if you file a bug report or contact op5 Support.</p></div>';
	} else {
		// by special casing this here once, we save some support time every time
		// log data clobbers a customers hard drive
		$content .= "<p>We failed to save the debug information to a filename in '$tmp_dir'. Please make sure that the permissions of that folder are correct, and that your hard drive isn't full.</p></div>";
	}
	$content .= $crash_info;
	unset($tmp_dir);
}

echo $content;
