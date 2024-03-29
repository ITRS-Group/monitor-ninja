<?php
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class ExpParser_SearchFilter_Test extends \PHPUnit\Framework\TestCase {
	/* ******
	 * Test correct simple queries for table access
	 */
	public function test_host() {
		$parser = $this->run_test('h:kaka', array( 'filters'=>array(
				'hosts'=>array(array('kaka'))
				) ) );
	}

	public function test_service() {
		$parser = $this->run_test('s:kaka', array( 'filters'=>array(
				'services'=>array(array('kaka'))
				) ) );
	}

	public function test_comments() {
		$parser = $this->run_test('c:kaka', array( 'filters'=>array(
				'comments'=>array(array('kaka'))
				)) );
	}

	public function test_status() {
		$parser = $this->run_test('si:kaka', array( 'filters'=>array(
				'_si'=>array(array('kaka'))
				)) );
	}

	public function test_hostgroups() {
		$parser = $this->run_test('hg:kaka', array( 'filters'=>array(
				'hostgroups'=>array(array('kaka'))
				)) );
	}

	public function test_servicegroups() {
		$parser = $this->run_test('sg:kaka', array( 'filters'=>array(
				'servicegroups'=>array(array('kaka'))
				)) );
	}

	/* ******
	 * Test simple queries with whitespace arguments and or
	 */
	public function test_space_argument() {
		$parser = $this->run_test('h:kaka boll or kalles serviceverkstad ', array( 'filters'=>array(
				'hosts'=>array(array('kaka boll','kalles serviceverkstad'))
				)) );
	}

	/* ******
	 * Test correct queries with boolean operators
	 */
	public function test_same_and() {
		$parser = $this->run_test('h:hostkaka and h:hostkoko', array( 'filters'=>array(
				'hosts'=>array(array('hostkaka'),array('hostkoko')),
				) ) );
	}

	public function test_diff_and() {
		$parser = $this->run_test('h:hostkaka and s:svckaka', array( 'filters'=>array(
				'hosts'=>array(array('hostkaka')),
				'services'=>array(array('svckaka'))
				) ) );
	}

	public function test_or() {
		$parser = $this->run_test('h:hostkaka or hostkoko', array( 'filters'=>array(
				'hosts'=>array(array('hostkaka', 'hostkoko'))
				) ) );
	}

	/* ******
	 * Test correct queries with autocompletion extraction of last fields
	 */
	public function test_autocomplete_first() {
		$parser = $this->run_test('h:kaka', array('filters'=>array('hosts'=>array(array('kaka')) )) );
		$this->assertEquals( $parser->getLastString(), 'kaka', "Autocomplete: doesn't return correct string" );
		$this->assertEquals( $parser->getLastObject(), 'hosts', "Autocomplete: doesn't return correct object type" );
	}

	public function test_autocomplete_or() {
		$parser = $this->run_test('h:kaka or boll', array('filters'=>array('hosts'=>array(array('kaka','boll')) )) );
		$this->assertEquals( $parser->getLastString(), 'boll', "Autocomplete: doesn't return correct string" );
		$this->assertEquals( $parser->getLastObject(), 'hosts', "Autocomplete: doesn't return correct object type" );
	}

	public function test_autocomplete_and() {
		$parser = $this->run_test('h:kaka and s:boll', array('filters'=>array(
				'hosts'=>array(array('kaka')),
				'services'=>array(array('boll'))
				 )) );
		$this->assertEquals( $parser->getLastString(), 'boll', "Autocomplete: doesn't return correct string" );
		$this->assertEquals( $parser->getLastObject(), 'services', "Autocomplete: doesn't return correct object type" );
	}

	/* *******
	 * Test correct queries with limit
	 */
	public function test_limit() {
		$this->run_test('h:kaka limit=13', array(
				'filters'=>array('hosts'=>array(array('kaka')) ),
				'limit' => 13
				) );
	}

	public function test_fail_args() {
		$this->run_test('h:kaka limit=13', array(
				'filters'=>array('hosts'=>array(array('kaka')) ),
				'limit' => 13
				) );
	}

	/* ******
	 * Test incorrect tables
	 */
	public function test_invalid_table() {
		try {
			$this->run_test('x:doesntexist', false);
			$this->fail( 'Should have thrown an exception');
		}
		catch( ExpParserException $e ) {
			$this->assertTrue(
					1===preg_match('/^Unexpected token.*$/',$e->getMessage()),
					'Incorrect Exception for unexpected token: '.$e->getMessage()
					);
		}
	}

	/* ******
	 * Test incomplete limit
	 */
	public function test_incomplete_limit() {
		try {
			$this->run_test('h:doesntexist limit=', false);
			$this->fail( 'Should have thrown an exception');
		}
		catch( ExpParserException $e ) {
			$this->assertTrue(
					1===preg_match('/^Unexpected token.*expected number.*$/',$e->getMessage()),
					'Incorrect Exception for unexpected token: '.$e->getMessage()
					);
		}
	}

	/* ******
	 * Test case sensitivity
	 */
	public function test_case_uppercase_and() {
		$parser = $this->run_test('h:hostkaka AND h:hostkoko', array( 'filters'=>array(
				'hosts'=>array(array('hostkaka'),array('hostkoko')),
				) ) );
	}
	public function test_case_uppercase_or() {
		$parser = $this->run_test('h:hostkaka OR hostkoko', array( 'filters'=>array(
				'hosts'=>array(array('hostkaka', 'hostkoko'))
				) ) );
	}
	public function test_case_mixcase_and() {
		$parser = $this->run_test('h:hostkaka aNd h:hostkoko', array( 'filters'=>array(
				'hosts'=>array(array('hostkaka'),array('hostkoko')),
				) ) );
	}
	public function test_case_mixcase_or() {
		$parser = $this->run_test('h:hostkaka oR hostkoko', array( 'filters'=>array(
				'hosts'=>array(array('hostkaka', 'hostkoko'))
				) ) );
	}

	/* ******
	 * Internal library
	 */
	private function run_test( $query, $expect ) {
		$parser = new ExpParser_SearchFilter(array(
			'h'  => 'hosts',
			's'  => 'services',
			'c'  => 'comments',
			'hg' => 'hostgroups',
			'sg' => 'servicegroups',
			'si' => '_si'
			));
		$result = $parser->parse( $query );
		$this->assertEquals( $result, $expect, "SearchFilter query '$query' doesn't match expected result." );
		return $parser;
	}
}
