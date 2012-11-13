Search_Controller

<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Search_Ajax_Test extends TapUnit {
	protected $controller = false; /* Controller to test */
	
	public function setUp() {
		$this->controller = new Ajax_Controller();
	}

	/*
	 * Those tests should test how the livesearch generates queries.
	 * 
	 * Tests handling the syntax of the filter shoudl be in expparser_searchfilter_Test,
	 * This is about columns and generation oh the query, and wildcard
	 */
	
	/* *****
	 * Test simple table access
	 */
	public function test_host() {
		$this->run_test('h:kaka', array( 'hosts', 'kaka', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'kaka' ) )
		) ) );
	}
	public function test_service() {
		$this->run_test('s:kaka', array( 'services', 'kaka', array(
				'columns' => array( 'description', 'host_name' ),
				'filter'  => array( 'description' => array( '~~' => 'kaka' ) )
		) ) );
	}
	public function test_hostgroup() {
		$this->run_test('hg:kaka', array( 'hostgroups', 'kaka', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'kaka' ) )
		) ) );
	}
	public function test_servicgroup() {
		$this->run_test('sg:kaka', array( 'servicegroups', 'kaka', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'kaka' ) )
		) ) );
	}
	public function test_comment() {
		$this->run_test('c:kaka', array( 'comments', 'kaka', array(
				'columns' => array( 'comment_data', 'host_name' ),
				'filter'  => array( 'comment_data' => array( '~~' => 'kaka' ) )
		) ) );
	}
	
	/* ******
	 * Test second parameter or
	 */
	public function test_host_or() {
		$this->run_test('h:kaka or boll', array( 'hosts', 'boll', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'boll' ) )
		) ) );
	}
	
	/* ******
	 * Test second parameter and
	 */
	public function test_host_and() {
		$this->run_test('h:kaka and h:boll', array( 'hosts', 'boll', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'boll' ) )
		) ) );
	}
	
	/* ******
	 * Test second parameter and different
	 */
	public function test_host_service_and() {
		$this->run_test('h:kaka and s:boll', array( 'services', 'boll', array(
				'columns' => array( 'description', 'host_name' ),
				'filter'  => array( 'description' => array( '~~' => 'boll' ) )
		) ) );
	}
	
	/* ******
	 * Wildcards
	 */
	public function test_host_wildcard() {
		$this->run_test('h:kaka%boll', array( 'hosts', 'kaka%boll', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'kaka.*boll' ) )
		) ) );
	}
	public function test_host_or_wildcard() {
		$this->run_test('h:kaka%boll or cykel%styre', array( 'hosts', 'cykel%styre', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'cykel.*styre' ) )
		) ) );
	}
	
	/* ******
	 * Simple query, without h:/s:
	 */
	public function test_simple() {
		$this->run_test('hopp', array( 'hosts', 'hopp', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'hopp' ) )
		) ) );
	}
	public function test_simple_wildcard() {
		$this->run_test('hopp%tjopp', array( 'hosts', 'hopp%tjopp', array(
				'columns' => array( 'name' ),
				'filter'  => array( 'name' => array( '~~' => 'hopp.*tjopp' ) )
		) ) );
	}

	protected function run_test( $query, $expect ) {
		$result = $this->controller->global_search_build_filter( $query );
		
		if( $expect === false && $result === false ) {
			$this->ok();
			return;
		}
		list( $type, $name, $settings, $options ) = $result;
		
		$this->ok_eq( array( $type, $name, $options ), $expect, "SearchFilter query '$query' doesn't match expected result." );
	}
}