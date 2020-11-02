<?php
require_once(__DIR__ . '/../system/helpers/debug.php');
require_once(__DIR__ . '/../system/helpers/html.php');

if (!class_exists('Kohana')) {
	// Mock Kohana::lang() if Kohana doesn't exists (i.e. test is run locally).
	class Kohana {
		public static function lang($key, $file, $line) {
			return sprintf('<samp>%s <strong>[%s]:</strong></samp>', $file, $line);
		}
	}
}

/**
 * Tests the debug helper class.
 *
 * @package NINJA
 * @author  op5
 * @license GPL
 */
class Debug_Helper_Test extends PHPUnit_Framework_TestCase {

	private $backtrace;

	protected function setUp() {
		$this->backtrace = array(
			array(
				'file'     => '/hej/hopp/vetej/kanske.php',
				'line'     => 8,
				'class'    => 'MasterClass',
				'type'     => '->',
				'function' => 'mupp_function',
				'args'     => array('jag', 'du')
			),
			array(
				'file'     => '/hej/hopp/vetej/kanske.php',
				'line'     => 8,
				'class'    => 'Debug_Helper_Test',
				'type'     => '::',
				'function' => 'param_name_test_function',
				'args'     => array('jag', 'du', 'hopp')
			),
			array(
				'file'     => '/hej/hopp/vetej/kanske.php',
				'line'     => 8,
				'function' => 'param_name_test_global_function',
				'args'     => array('jag', 'du', 'hopp')
			)
		);
	}

	public function test_get_class_func_param_names() {
		$names = debug::get_func_param_names(
			'param_name_test_function', 'Debug_Helper_Test'
		);
		$this->assertSame(array('arg1', 'password', 'secret'), $names);
	}

	public function test_get_global_func_param_names() {
		$names = debug::get_func_param_names(
			'param_name_test_global_function', '/hej/hopp/'
		);
		$this->assertSame(array('arg1', 'password', 'secret'), $names);
	}

	public function test_format_backtrace() {
		$formatted_bt = debug::format_backtrace($this->backtrace, '/hej/hopp/');

		$expected = array(
			array(
				'file'     => 'vetej/kanske.php',
				'line'     => 8,
				'class'    => 'MasterClass',
				'type'     => '->',
				'function' => 'mupp_function',
				'args'     => array('... = jag', '... = du')
			),
			array(
				'file'     => 'vetej/kanske.php',
				'line'     => 8,
				'class'    => 'Debug_Helper_Test',
				'type'     => '::',
				'function' => 'param_name_test_function',
				'args'     => array(
					'arg1 = jag',
					'password = *****',
					'secret = *****'
				)
			),
			array(
				'file'     => 'vetej/kanske.php',
				'line'     => 8,
				'class'    => null,
				'type'     => null,
				'function' => 'param_name_test_global_function',
				'args'     => array(
					'arg1 = jag',
					'password = *****',
					'secret = *****'
				)
			)
		);

		$this->assertSame($expected, $formatted_bt);
	}

	public function test_print_backtrace_as_html() {
		ob_start();
		debug::print_backtrace_as_html($this->backtrace, '/hej/hopp/');
		$html = ob_get_clean();

		// Remove tabs and newlines.
		$html = preg_replace('/\s+/', ' ', $html);

		// Expected output without newlines and tabs. Not very readable but
		// it is at least a way to check that debug::print_backtrace_as_html()
		// prints correct html.
		$expected = '<ul class="backtrace"> <li> <samp>vetej/kanske.php ' .
			'<strong>[8]:</strong></samp> <pre> MasterClass->mupp_function( ' .
			'... = jag, ... = du ) </pre> </li> <li> <samp>vetej/kanske.php ' .
			'<strong>[8]:</strong></samp> <pre> Debug_Helper_Test::' .
			'param_name_test_function( arg1 = jag, password = *****, ' .
			'secret = ***** ) </pre> </li> <li> <samp>vetej/kanske.php ' .
			'<strong>[8]:</strong></samp> <pre> ' .
			'param_name_test_global_function( arg1 = jag, password = *****, ' .
			'secret = ***** ) </pre> </li> </ul>';

		$this->assertSame($expected, $html);
	}

	public function test_get_backtrace_as_string() {
		$str_backtrace = debug::get_backtrace_as_string($this->backtrace, '/hej/hopp/');

        $expected =
        	"vetej/kanske.php [8]: MasterClass->mupp_function( ... = jag, ... = du )\n" .
			"vetej/kanske.php [8]: Debug_Helper_Test::param_name_test_function( arg1 = jag, password = *****, secret = ***** )\n" .
			"vetej/kanske.php [8]: param_name_test_global_function( arg1 = jag, password = *****, secret = ***** )";

		$this->assertSame($expected, $str_backtrace);
	}

	public function test_safe_print_r() {
		$debug_this = array(
			array(
				array(
					array(
						new stdClass(),
						array(
							'deep' => 'datastructure',
							array('data' => 'should not be seen')
						)
					)
				)
			), 54, "hej hej hej"
		);

		$output = debug::safe_print_r($debug_this);
		$no_whitespace = preg_replace('/\s+/', '', $output);

		$expected = 'Array([0]=>Array([0]=>Array([0]=>Array([0]=>stdClass(),' .
			'[1]=>Array([deep]=>datastructure,[0]=>Array([data]=>**MORE**)))))' .
			',[1]=>54,[2]=>hejhejhej)';

		$this->assertSame($expected, $no_whitespace);
	}

	/**
	 * Helper function for testing debug::get_func_param_names().
	 */
	private function param_name_test_function($arg1, $password, $secret) {}
}

/**
 * Helper function for testing debug::get_func_param_names().
 */
function param_name_test_global_function($arg1, $password, $secret) {}