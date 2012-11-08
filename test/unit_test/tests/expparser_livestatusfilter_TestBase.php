<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
abstract class ExpParser_LivestatusFilter_TestBase extends TapUnit {
	/* ******
	 * Tests for all operators
	 */
	public function test_eq_str()  { $this->run_test( 'col = "val"',  array("Filter: col = val" ) ); }
	public function test_re_str()  { $this->run_test( 'col ~ "val"',  array("Filter: col ~ val" ) ); }
	public function test_eqi_str() { $this->run_test( 'col =~ "val"', array("Filter: col =~ val") ); }
	public function test_rei_str() { $this->run_test( 'col ~~ "val"', array("Filter: col ~~ val") ); }
	public function test_lt_str()  { $this->run_test( 'col < "val"',  array("Filter: col < val" ) ); }
	public function test_gt_str()  { $this->run_test( 'col > "val"',  array("Filter: col > val" ) ); }
	public function test_lte_str() { $this->run_test( 'col <= "val"', array("Filter: col <= val") ); }
	public function test_gte_str() { $this->run_test( 'col >= "val"', array("Filter: col >= val") ); }

	public function test_eq_int()  { $this->run_test( 'col = 13',  array("Filter: col = 13" ) ); }
	public function test_re_int()  { $this->run_fail( 'col ~ 13',  '/^Unexpected token, expected string at [0-9]+$/' ); }
	public function test_eqi_int() { $this->run_fail( 'col =~ 13', '/^Unexpected token, expected string at [0-9]+$/' ); }
	public function test_rei_int() { $this->run_fail( 'col ~~ 13', '/^Unexpected token, expected string at [0-9]+$/' ); }
	public function test_lt_int()  { $this->run_test( 'col < 13',  array("Filter: col < 13" ) ); }
	public function test_gt_int()  { $this->run_test( 'col > 13',  array("Filter: col > 13" ) ); }
	public function test_lte_int() { $this->run_test( 'col <= 13', array("Filter: col <= 13") ); }
	public function test_gte_int() { $this->run_test( 'col >= 13', array("Filter: col >= 13") ); }

	/* ******
	 * Tests for all operators negated
	 */
	public function test_eq_str_neg()  { $this->run_test( 'col != "val"',  array("Filter: col != val" ) ); }
	public function test_re_str_neg()  { $this->run_test( 'col !~ "val"',  array("Filter: col !~ val" ) ); }
	public function test_eqi_str_neg() { $this->run_test( 'col !=~ "val"', array("Filter: col !=~ val") ); }
	public function test_rei_str_neg() { $this->run_test( 'col !~~ "val"', array("Filter: col !~~ val") ); }
	public function test_lt_str_neg()  { $this->run_test( 'col !< "val"',  array("Filter: col !< val" ) ); }
	public function test_gt_str_neg()  { $this->run_test( 'col !> "val"',  array("Filter: col !> val" ) ); }
	public function test_lte_str_neg() { $this->run_test( 'col !<= "val"', array("Filter: col !<= val") ); }
	public function test_gte_str_neg() { $this->run_test( 'col !>= "val"', array("Filter: col !>= val") ); }

	public function test_eq_int_neg()  { $this->run_test( 'col != 13',  array("Filter: col != 13" ) ); }
	public function test_re_int_neg()  { $this->run_fail( 'col !~ 13',  '/^Unexpected token, expected string at [0-9]+$/' ); }
	public function test_eqi_int_neg() { $this->run_fail( 'col !=~ 13', '/^Unexpected token, expected string at [0-9]+$/' ); }
	public function test_rei_int_neg() { $this->run_fail( 'col !~~ 13', '/^Unexpected token, expected string at [0-9]+$/' ); }
	public function test_lt_int_neg()  { $this->run_test( 'col !< 13',  array("Filter: col !< 13" ) ); }
	public function test_gt_int_neg()  { $this->run_test( 'col !> 13',  array("Filter: col !> 13" ) ); }
	public function test_lte_int_neg() { $this->run_test( 'col !<= 13', array("Filter: col !<= 13") ); }
	public function test_gte_int_neg() { $this->run_test( 'col !>= 13', array("Filter: col !>= 13") ); }

	/* ******
	 * Test boolean operations
	 */
	public function test_or() {
		$this->run_test( 'cola = "vala" or colb = "valb"', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"Or: 2"
				));
	}
	public function test_and() {
		$this->run_test( 'cola = "vala" and colb = "valb"', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"And: 2"
				));
	}

	/* ******
	 * Test several boolean operations
	 */

	public function test_or3() {
		$this->run_test( 'cola = "vala" or colb = "valb" or colc = "valc"', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"Filter: colc = valc",
				"Or: 3"
				));
	}
	public function test_and3() {
		$this->run_test( 'cola = "vala" and colb = "valb" and colc = "valc"', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"Filter: colc = valc",
				"And: 3"
				));
	}

	/* ******
	 * Test priorities
	 */

	public function test_or_and() {
		$this->run_test( 'cola = "vala" or colb = "valb" and colc = "valc"', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"Filter: colc = valc",
				"And: 2",
				"Or: 2"
				));
	}
	public function test_and_or() {
		$this->run_test( 'cola = "vala" and colb = "valb" or colc = "valc"', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"And: 2",
				"Filter: colc = valc",
				"Or: 2"
				));
	}

	/* ******
	 * Test paranthesis
	 */

	public function test_or_and_par() {
		$this->run_test( '(cola = "vala" or colb = "valb") and colc = "valc"', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"Or: 2",
				"Filter: colc = valc",
				"And: 2"
				));
	}
	public function test_and_or_par() {
		$this->run_test( 'cola = "vala" and (colb = "valb" or colc = "valc")', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"Filter: colc = valc",
				"Or: 2",
				"And: 2"
				));
	}

	/* ******
	 * Test not
	 */

	public function test_not() {
		$this->run_test( 'not cola = "vala"', array(
				"Filter: cola = vala",
				"Negate:",
		));
	}
	public function test_not_prio() {
		$this->run_test( 'not cola = "vala" and colb = "valb"', array(
				"Filter: cola = vala",
				"Negate:",
				"Filter: colb = valb",
				"And: 2"
				));
	}

	/* ******
	 * Test whitespace tolerance
	 */
	public function test_whitespace() {
		$this->run_test( '  cola 	  =  "vala"or    colb   ="valb"  ', array(
				"Filter: cola = vala",
				"Filter: colb = valb",
				"Or: 2"
				));
	}

	/* ******
	 * Fail test: double ops
	 */
	public function test_fail_and_and() { $this->run_fail( 'a=1 and and b=2', '/^Unexpected token/' ); }
	public function test_fail_and_or()  { $this->run_fail( 'a=1 and or b=2',  '/^Unexpected token/' ); }
	public function test_fail_or_and()  { $this->run_fail( 'a=1 or and b=2',  '/^Unexpected token/' ); }
	public function test_fail_or_or()   { $this->run_fail( 'a=1 or or b=2',   '/^Unexpected token/' ); }

	/* ******
	 * Fail test: malplaced not
	*/
	public function test_fail_not_and() { $this->run_fail( 'a=1 not and b=2', '/^Expected end at/' ); }
	public function test_fail_not_end() { $this->run_fail( 'a=1 not',         '/^Expected end at/' ); }
	public function test_fail_not_par() { $this->run_fail( '(a=1 not)',       '/^Unexpected token, expected \)/'); }

	/* ******
	 * Fail test: binary op without rhs
	 */
	public function test_fail_and_end() { $this->run_fail( 'a=1 and',   '/^Unexpected token/' ); }
	public function test_fail_or_end()  { $this->run_fail( 'a=1 or',    '/^Unexpected token/' ); }
	public function test_fail_and_end_par() { $this->run_fail( '(a=1 and)', '/^Unexpected token/'); }
	public function test_fail_or_end_par()  { $this->run_fail( '(a=1 or)',  '/^Unexpected token/'); }

	/* ******
	 * Fail test: binary op in beginning
	 */
	public function test_fail_and_start()     { $this->run_fail( 'and a=2', '/^Unexpected token/' ); }
	public function test_fail_or_start()      { $this->run_fail( 'or a=2',  '/^Unexpected token/' ); }
	public function test_fail_and_start_par() { $this->run_fail( '(and a=1)', '/^Unexpected token/'); }
	public function test_fail_or_start_par()  { $this->run_fail( '(or a=1)',  '/^Unexpected token/'); }

	/* ******
	 * Internal library
	 */
	abstract protected function run_test( $query, $expect );

	private function run_fail( $query, $exception_match ) {
		try {
			$this->run_test( $query, false );
			$this->fail( 'Should have thrown an exception' );
		}
		catch( ExpParserException $e ) {
			$this->ok(
					1===preg_match($exception_match,$e->getMessage()),
					'Incorrect Exception for unexpected token: '.$e->getMessage()
					);
		}
	}
}