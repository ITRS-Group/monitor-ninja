<?php
/**
 * SQL Helper Test.
 *
 * @package    Unit_Test
 */
class sql_helper_Test extends \PHPUnit\Framework\TestCase {

	public function test_sqlor_empty_args() {
		$this->assertSame('1=0', sql::sqlor());
		$this->assertSame('1=0', sql::sqlor(false, '', null));
	}

	public function test_sqlor_single_clause() {
		$this->assertSame('(a = 1)', sql::sqlor('a = 1'));
	}

	public function test_sqlor_multiple_clauses() {
		$this->assertSame(
			'(a = 1) or (b = 2)',
			sql::sqlor('a = 1', false, 'b = 2')
		);
	}

	public function test_sqland_empty_args() {
		$this->assertFalse(sql::sqland());
		$this->assertFalse(sql::sqland(false, '', null));
	}

	public function test_sqland_single_clause() {
		$this->assertSame('(a = 1)', sql::sqland('a = 1'));
	}

	public function test_sqland_multiple_clauses() {
		$this->assertSame(
			'(a = 1) and (b = 2)',
			sql::sqland('a = 1', false, 'b = 2')
		);
	}

}
