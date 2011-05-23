
<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
# The constants usable for the 'flags' field
define('TAP_TODO', 1);
define('TAP_CRITICAL', 2); # doubles as a full-suite status flag
define('TAP_SKIP', 4);
# As per Heuristics Driven Development (Hi Masukomi!)

define('TAP_OK', 0); # all tests passed
define('TAP_WARNING', 1); # no test failed

define('TAP_FAIL', 'fail');# should be "not ok" according to TAP

/**
 * TAP-like testing API for PHP
 */
class phptap
{
	private $section_name;
	private $num_tests = 0;
	private $planned_tests = false;
	private $skip = false;
	private $skip_msg = false;
	private $skipped = 0;
	private $flags = 0;
	private $todo_msg = false;
	private $failed = 0;
	private $summarized = false;
	private $ansi_colors = false;
	private $color_reset = "\033[0m";
	private $tcount = array();
	private $is_done = false;
	private $colors;
	private $ind_lvl = 0;
	private $parent = false;
	private $use_colors = true;
	private $suite = '';
	private $show_diag = false;
	private $have_header = false;
	private $verbose = 0; # print buggy, todo, skip etc by default
	/** Use real TAP-compatible output */
	public $tap_compat = false;
	/** True if run from command-line. False otherwise */
	public $cli = true;

	/**
	 * constructor
	 */
	function phptap($desc = false)
	{
		$this->cli = PHP_SAPI === 'cli';
		if (!$this->cli) {
			$this->use_colors = false;
			if (isset($_REQUEST['TAP_V']))
				$this->verbose = intval($_REQUEST['TAP_V']);
		}
		else {
			# CLI testing
			#if (defined('STDOUT'))
			#	$this->use_colors = posix_isatty(STDOUT);
			$this->parse_args(true);
		}
		$this->init($desc, true);
	}

	/*** non-public functions ***/
	private function _print_one($what, $msg)
	{
		$this->print_header(true);
		$this->show_diag = true;

		if ($this->tap_compat && $what === 'fail')
			$what = 'not ok';

		if ($this->cli) {
			$this->_indent();
			$cwhat = $this->_colorize($what);
			if ($this->tap_compat)
				echo "$what $this->num_tests # $msg\n";
			else
				echo $this->_colorize($what) . ": $msg\n";
		}
		else {
			$color = '';
			if (isset($this->html_colors[$what]))
				$color = "bgcolor='" . $this->html_colors[$what] . "'";

			$css_class = str_replace(' ', '_', $what);
			echo "  <tr class='phptap_$css_class'>\n";
			echo "    <td style=\"border: 1px solid black; padding-left: 2px; padding-right: 2px;\" $color class='phptap_$css_class'>$what</td>\n";
			echo "    <td style=\"border-bottom: 1px solid black;\" class='phptap_msg_$css_class'>$msg</td>\n";
			echo "  </tr>\n";
		}
	}

	/**
	 * Sets the test-suite's overall status based on single test
	 */
	private function get_status()
	{
		if (!empty($this->tcount[TAP_FAIL]))
			return TAP_CRITICAL;

		if (empty($this->tcount['ok']) && empty($this->tcount['fixed']))
			return TAP_WARNING;
		$ok = empty($this->tcount['ok']) ? 0 : $this->tcount['ok'];
		$fixed = empty($this->tcount['fixed']) ? 0 : $this->tcount['fixed'];
		if ($ok + $fixed !== $this->num_tests)
			return TAP_WARNING;

		return TAP_OK;
	}

	private function _colorize($what, $color = false)
	{
		if ($color === false)
			$color = $what;

		if (!$this->use_colors || empty($this->colors[$color]) || !$this->cli)
			return $what;

		return $this->colors[$color] . $what . $this->color_reset;
	}

	private function _indent($ind_lvl = 0)
	{
		if (!($this->ind_lvl + $ind_lvl))
			return;

		$buf = '';
		$indent = $this->ind_lvl + $ind_lvl;
		while ($indent--)
			$buf .= "  ";

		echo $buf;
	}

	/**
	 * Remove all html tags from a string
	 */
	private function _strip_html(&$str)
	{
		$str = preg_replace('/<([^>]*)>/', '', $str);
		return $str;
	}

	private function _summarize()
	{
		if (!$this->have_header)
			return;

		$status = $this->get_status();

		$suite = $this->parent ? "$this->suite: " : '';
		if (!$this->cli)
			$summary = $suite;
		else
			$summary = $this->_colorize("### $suite", $status);

		if (!empty($this->tcount['ok']) && count($this->tcount) === 1) {
			$summary .= $this->_colorize("All $this->num_tests tests passed", 'brightgreen');
		}
		else {
			$summary .= "total: $this->num_tests";
			foreach ($this->tcount as $cat => $num)
				$summary .= ", " . $this->_colorize("$cat: $num", $cat);
		}

		$this->_indent();
		if ($this->cli)
			echo "$summary\n";
		else {
			$color = '';
			if (isset($this->colors[$status]))
				$color = "bgcolor='#" . $this->colors[$status] . "'";

			$has_failures = $this->failed ? '_fail' : '';
			echo "<tr><td $color colspan=2 class='phptap_summary$has_failures'>";
			echo "$summary</tr></td></table>\n";
		}
	}

	/*** public functions (ie, the api) ***/
	/**
	 * Initialize the tap-object
	 * @param $suite Test-suite description
	 * @param $colors_too Reset colors too
	 */
	public function init($suite = false, $colors_too = false)
	{
		$this->skip_end();
		$this->todo_end();
		$this->tcount = array();
		$this->status = 'AOK';
		$this->num_tests = 0;
		$this->have_header = false;
		if ($colors_too) {
			$this->ansi_colors = array
				('red' => "\033[31m",
				 'brightred' => "\033[31m\033[1m",
				 'green' => "\033[32m",
				 'gray' => "\033[30m\033[1m", # "bright" black
				 'brightgreen' => "\033[32m\033[1m",
				 'brown' => "\033[33m",
				 'yellow' => "\033[33m\033[1m",
				 'blue' => "\033[34m",
				 'brightblue' => "\033[34m\033[1m",
				 'pink' => "\033[35m",
				 'brightpink' => "\033[35m\033[1m",
				 'cyan' => "\033[36m",
				 'brightcyan' => "\033[36m\033[1m",
				 );
			$this->html_colors = array
				('red' => '#dd0000',
				 'brightred' => 'ff0000',
				 'green' => '00cc00',
				 'gray' => 'cccccc',
				 'brightgreen' => '00ff00',
				 'brown' => 'bbaa44',
				 'yellow' => 'ffff00',
				 'blue' => '0000ff',
				 'brightblue' => '6699ff',
				 'pink' => 'cc00cc',
				 'brightpink' => 'ff00ff',
				 'cyan' => '3399cc',
				 'brightcyan' => '66bbff',
				 );
			if ($this->cli)
				$this->colors = &$this->ansi_colors;
			else
				$this->colors = &$this->html_colors;

			$this->set_color('buggy', 'brightpink');
			$this->set_color('ok', 'green');
			$this->set_color('fixed', 'brightgreen');
			$this->set_color('todo', 'yellow');
			$this->set_color('broken', 'todo');
			$this->set_color('still broken', 'todo');
			$this->set_color('skip', 'pink');
			$this->set_color(TAP_FAIL, 'red');
			$this->set_color(TAP_OK, 'brightgreen');
			$this->set_color(TAP_WARNING, 'yellow');
			$this->set_color(TAP_CRITICAL, 'brightred');
			$this->set_color('description', 'brightblue');
			$this->set_color('trace', 'yellow');
		}

		if (!empty($suite))
			$this->print_header($suite);
	}

	/**
	 * Parse commonly used arguments for testing programs
	 * that use the PHPTAP class
	 */
	public function parse_args($num_args = false, $args = false)
	{
		if ($num_args = true) {
			global $argc, $argv;
			$num_args = $argc;
			$args = $argv;
		}
		for ($i = 1; $i < $num_args; $i++) {
			$arg = $args[$i];
			if ($arg === '-v')
				$this->verbose++;
			elseif ($arg === '-t' || $arg === '--tap')
				$this->enable_tap_compatibility();
			elseif ($arg === '--use-colors')
				$this->use_colors = 1;
		}
	}

	/**
	 * Initialize a sub-suite
	 * @param $desc Description for the sub-suite
	 * @return A new TAP object, to be used for the sub-suite's tests
	 */
	public function sub_init($desc = false)
	{
		$sub = clone($this);
		$sub->parent = $this;
		$sub->ind_lvl = $this->ind_lvl + 1;
		$sub->init($desc);
		return $sub;
	}

	/**
	 * End a sub-suite
	 * @return The parent TAP object
	 */
	public function sub_end()
	{
		$this->done();
		return $this->parent;
	}

	/** List which colors are set */
	public function show_colors()
	{
		$color_reset = "\033[0m";
		if (!$this->cli)
			echo "<table>\n";
		foreach ($this->colors as $name => $color) {
			if ($this->cli)
				echo "$color$name$color_reset\n";
			else
				echo "<tr><td bgcolor='$color'>$name</td></tr>\n";
		}
		if (!$this->cli)
			echo "</table>\n";
	}

	/**
	 * Set a color for a certain description
	 * @param $str The description (ie, 'fail')
	 * @param $color The color to use
	 */
	public function set_color($str = false, $color = false)
	{
		if ($str === false || $color === false || !isset($this->colors[$color]))
			return;
		$this->colors[$str] = $this->colors[$color];
	}

	/**
	 * Plan a number of tests (default plan is to have no plan)
	 * @param $num The number of tests planned
	 */
	public function plan($num = false)
	{
		if ($num === false)
			return;
		$this->planned_tests = $num;
	}

	/**
	 * Mark a number of tests as 'todo'
	 * @param $msg A message to print for the failing tests
	 * @param $num If given, mark a pre-determined number of tests as todo
	 */
	public function todo_start($msg = false, $num = true)
	{
		$this->todo = $num;
		$this->todo_msg = $msg;
	}

	/**
	 * Stop marking tests as todo
	 * @param $output Currently unused
	 */
	public function todo_end($output = true)
	{
		$this->flags &= ~TAP_TODO;
		$this->todo_msg = false;
		$this->todo_ents = 0;
	}

	/**
	 * Mark a number of tests as 'skipped'
	 * @param $msg A message to print for the skipped tests
	 * @param $num If given, mark a pre-determined number of tests as skipped
	 */
	public function skip_start($msg = false, $num = true)
	{
		$this->flags |= TAP_SKIP;
		$this->skip = $num;
		$this->skip_msg = $msg;
	}

	/**
	 * Skip no more tests
	 * @param $output If true (default), print a message saying
	 * how many tests were skipped
	 */
	public function skip_end($output = true)
	{
		if ($output && $this->skipped && !$this->verbose) {
			$msg = sprintf("Skipped %d test%s%s", $this->skipped,
				$this->skipped > 1 ? 's' : '',
				$this->skip_msg ? ": $this->skip_msg" : "");
			$this->_print_one('skip', $msg);
		}

		$this->flags &= ~TAP_SKIP;
		$this->skip_msg = false;
		$this->skipped = 0;
	}

	/**
	 * Print a test-suite header (description)
	 * @param $suite The name/description to print
	 */
	public function print_header($suite)
	{
		# don't print header twice
		if ($this->have_header)
			return;

		if ($suite !== true)
			$this->suite = $suite;

		# print all parent headers recursively
		if ($suite === true) {
			if ($this->parent)
				$this->parent->print_header(true);
		}

		# skip subsuites unless forced or verbose
		if ($this->parent && !$this->verbose && $suite !== true)
			return;

		# we're actually going to print this one
		$this->have_header = true;

		$suite = $this->parent ? 'subsuite: ' : '';
		$suite .= $this->suite;

		if ($this->cli) {
			$this->_indent();
			echo $this->_colorize("### $suite ###", 'description');
			echo "\n";
			return;
		}

		$color = "bgcolor='#" . $this->colors['description'] . "'";
		echo "<tr><td colspan=100>\n";
		$margin = $this->parent ? 'margin-left: 20px' : '';
		echo "<table style='border: 1px solid black; $margin;' class='phptap_results'><tr>\n";
		echo "  <td $color colspan=100>\n";
		echo $suite . "\n";
		echo "</td></tr>\n";
	}

	/**
	 * The workhorse. All test_* functions end up here
	 * @param $result The test-result. Must be 'true' for test to pass
	 * @param $msg A description of this particular test
	 * @param $flags OR'ed bitflags for this test (TAP_TODO etc)
	 * @return TRUE if test passed. FALSE otherwise.
	 */
	public function ok($result, $msg, $flags = 0)
	{
		$this->show_diag = false;

		# we add global flags once
		$flags |= $this->flags;

		$this->num_tests++;
		$what = 'ok';

		if ($this->planned_tests && $this->num_tests > $this->planned_tests)
			echo "Test #$this->num_tests out of $this->planned_tests. What voodoo is this?!\n";

		if ($flags & TAP_SKIP) {
			$what = 'skip';
			if ($this->skip) {
				$this->skipped++;
				if ($this->skipped === $this->skip)
					$this->skip_end();
			}
		}
		elseif (!is_bool($result))
			$what = 'buggy';
		elseif ($result !== true)
			$what = $flags & TAP_TODO ? 'todo' : TAP_FAIL;
		elseif ($flags & TAP_TODO)
			$what = 'fixed';

		if (!isset($this->tcount[$what]))
			$this->tcount[$what] = 0;
		$this->tcount[$what]++;

		switch ($what) {
		 case 'ok':
			$should_print = ($this->verbose > 1);
			break;
		 case TAP_FAIL: case 'buggy':
			$should_print = true;
			break;
		 default:
			$should_print = ($this->verbose !== 0);
		}

		if ($should_print) {
			$this->_print_one($what, $msg);

			if ($what === 'buggy')
				$this->diag('phptap::ok() not passed a boolean value');
		}
		if ($what !== 'ok') {
			$trace = debug_backtrace();
			$last = false;
			foreach ($trace as $stk) {
				if ($stk['file'] === __FILE__) {
					continue;
				}
				if (!empty($stk['object']) && $stk['object'] === $this) {
					$last = $stk;
					continue;
				}
				break;
			}

			# $stk holds the call-point inside the library,
			# unless this was done from the main code body
			if (!empty($stk['class']) && $stk['class'] === get_class($this)) {
				$func = '(main)';
			} else {
				$func = '';
				if (!empty($stk['class']))
					$func = $stk['class'] . $stk['type'];
				$func .= $stk['function'] . "()";
			}

			# $last holds the call-point reaching into the library
			# with the filename and linenumber of the file making
			# the call
			$fname = $this->_colorize(basename($last['file']), 'trace');
			$line = $this->_colorize($last['line'], 'trace');
			$func = $this->_colorize($func, 'trace');
			$this->diag("In $func at $fname on line $line");
		}

		return $result === true;
	}

	/**
	 * Verify that the first variable is greater than the second
	 * @param $a The first variable (which should be the greater)
	 * @param $b The second variable (which should be the lesser)
	 * @param $msg A description of this particular test
	 * @param $flags OR'ed bitflags for this test (TAP_TODO etc)
	 * @return TRUE if test passed. FALSE otherwise.
	 */
	public function ok_gt($a, $b, $msg, $flags = false)
	{
		$ret = $this->ok($a > $b, $msg, $flags);
		if (!$ret)
			$this->diag(array($a, '<=', $b));

		return $ret;
	}

	/**
	 * Verify that two variables are identical
	 * @param $a The first variable
	 * @param $b The second variable
	 * @param $msg A description of this particular test
	 * @param $flags OR'ed bitflags for this test (TAP_TODO etc)
	 * @return TRUE if test passed. FALSE otherwise.
	 */
	public function ok_id($a, $b, $msg, $flags = false)
	{
		$ret = $this->ok($a === $b, $msg, $flags);
		if (!$ret)
			$this->diag(array($a, '!==', $b));

		return $ret;
	}

	/**
	 * Verify that two variables are 'equal'
	 * @param $a The first variable
	 * @param $b The second variable
	 * @param $msg A description of this particular test
	 * @param $flags OR'ed bitflags for this test (TAP_TODO etc)
	 * @return TRUE if test passed. FALSE otherwise.
	 */
	public function ok_eq($a, $b, $msg, $flags = false)
	{
		$ret = $this->ok($a == $b, $msg, $flags);
		if (!$ret)
			$this->diag(array($a, '!=', $b));

		return $ret;
	}

	/**
	 * Print diagnostic message (part of semi-official TAP API)
	 * @param $msg The message to print (can be arrays)
	 * @param $diag_depth Diagnostic depth (used internally)
	 */
	public function diag($msg, $diag_depth = 0)
	{
		if (!$this->show_diag)
			return;
		$ary = is_array($msg) ? $msg : array($msg);
		if (!$this->cli && !$diag_depth)
			echo "<tr><td colspan=100><pre>\n";
		foreach ($ary as $k => $v) {
			$type = gettype($v);
			if (is_string($v))
				$v = trim($v);
			elseif (is_bool($v))
				$v = $v ? 'true' : 'false';

			$this->_indent(1);
			$ary_ind = '';
			for ($d = 0; $d < $diag_depth; $d++)
				$ary_ind .= '  ';
			$fmt = '# ' . $ary_ind . "%s\n";
			if (is_array($v)) {
				printf($fmt, "$k => array(");
				$this->diag($v, $diag_depth + 1);
				$this->_indent(1);
				echo "# $ary_ind)\n";
				continue;
			}
			if ($type !== 'string')
				$out = "($type): $v";
			else
				$out = "$v";
			if (is_int($k))
				printf($fmt, $out);
			else
				printf($fmt, "$k = $out");
		}
		if (!$this->cli && !$diag_depth)
			echo "</pre></td></tr>\n";
	}

	/**
	 * Mark a test as 'failed'
	 * @param $msg A description of this particular test
	 * @param $flags OR'ed bitflags for this test (TAP_TODO etc)
	 * @return Always FALSE
	 */
	public function fail($msg, $flags = false)
	{
		return $this->ok(false, $msg, $flags);
	}

	/**
	 * Mark a test as 'passed'
	 * @param $msg A description of this particular test
	 * @param $flags OR'ed bitflags for this test (TAP_TODO etc)
	 * @return Always TRUE
	 */
	public function pass($msg, $flags = false)
	{
		return $this->ok(true, $msg, $flags);
	}

	/**
	 * Test if one array is a subset of another
	 * @param $a The full array
	 * @param $b The subset array
	 * @param $msg A description of this particular test
	 * @param $flags OR'ed bitflags for this test (TAP_TODO etc)
	 * @return TRUE if $b is a subset of $a. FALSE otherwise
	 */
	public function is_array_subset($a, $b, $msg, $flags = false)
	{
		foreach ($a as $k => $v) {
			if (!isset($b[$k]) || $b[$k] != $v)
				$this->test_fail($msg);
		}
		return $this->test_pass($msg, $flags);
	}

	/**
	 * Finish a test-suite and all its sub-suits
	 * @param $do_exit If true, exit with the current test-suite status
	 * @return 2 if there are tests with 'failed' status.
	 *         1 if not all tests passed.
	 *         0 if all tests passed.
	 */
	public function done($do_exit = false)
	{
		if ($do_exit === true)
			exit($this->done(false));

		$this->_summarize();

		if ($this->parent) {
			$this->parent->num_tests += $this->num_tests;
			foreach ($this->tcount as $cat => $num) {
				if (!isset($this->parent->tcount[$cat]))
					$this->parent->tcount[$cat] = 0;
				$this->parent->tcount[$cat] += $num;
			}
		}

		$status = $this->get_status();
		$this->init();

		return $status;
	}

	private function self_test_strip_html()
	{
		$sub = $this->sub_init('strip_html()');
		$correct = 'testing';
		$foo = array('testing' => "don't modify strings without html",
					 '<a href=foobarnisse>testing' => 'strip leading tags',
					 'testing<td>' => "strip trailing tags",
					 'testing<td><td>' => 'strip chained tags',
					 'tes<td>ting' => 'strip embedded tags',
					 '<td>tes<td>ting<td>' => 'strip leading, embedded and trailing tags');
		$sub->plan(count($foo));
		foreach ($foo as $str => $msg) {
			$sub->_strip_html($str);
			$sub->ok($str === $correct, $msg);
		}
		return $sub->done();
	}

	/**
	 * Run phptap selftests.
	 * WARNING WARNING WARNING
	 * This destroyes the current test-suite. Do NOT run this after
	 * having initialized your own test-suite.
	 */
	public function self_test()
	{
		$this->init('PHPTAP selftests');
		$this->ok($this->self_test_strip_html($this) === 0, "strip_html()");
		return $this->done();
	}

	/** Print the entire TAP object (for debugging phptap itself) */
	public function debug_print()
	{
		if (!$this->cli)
			echo "<pre>\n";
		$ansi_colors = $this->ansi_colors;
		$html_colors = $this->html_colors;
		$this->html_colors = $this->ansi_colors = array();
		print_r($this);
		$this->ansi_colors = $ansi_colors;
		$this->html_colors = $html_colors;
		if (!$this->cli)
			echo "</pre>\n";
	}
}
?>
