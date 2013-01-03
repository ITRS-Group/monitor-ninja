<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Search_Test extends TapUnit {
	protected $controller = false; /* Controller to test */
	
	public function setUp() {
		$this->controller = new Search_Controller();
	}

	/*
	 * Those tests should test how the search from the ExpParser filter is converted to a live status query
	 * 
	 * Tests handling the syntax of the filter shoudl be in expparser_searchfilter_Test,
	 * This is about columns and generation oh the query, and wildcard
	 */
	
	/* *****
	 * Test simple table access
	 */
	public function test_host() {
		$this->run_test('h:kaka', '[hosts] (name ~~ "kaka" or address ~~ "kaka")' );
	}
	public function test_service() {
		$this->run_test('s:kaka', '[services] (description ~~ "kaka" or display_name ~~ "kaka")' );
	}
	public function test_hostgroups() {
		$this->run_test('hg:kaka', '[hostgorups] (name ~~ "kaka" or alias ~~ "kaka")' );
	}
	public function test_servicegroups() {
		$this->run_test('sg:kaka', '[servicegroups] (name ~~ "kaka" or alias ~~ "kaka")' );
	}
	
	/* ******
	 * Test wildcard search
	 */
	public function test_wildcard() {
		$this->run_test('h:aaa%bbb', '[hosts] (name ~~ "aaa.*bbb" or address ~~ "aaa.*bbb")' );
	}
	
	
	/* ******
	 * Test combined host/service (services by hosts)
	 */
	public function test_host_serivce() {
		$this->run_test('h:kaka and s:pong', '[services] (description ~~ "pong" or display_name ~~ "pong") and (host.name ~~ "kaka" or host.address ~~ "kaka")' );
	}
	
	/* ******
	 * Test limit
	 */
	public function test_host_limit() {
		// Limit should be ignored...
		$this->run_test('h:kaka limit=24', '[hosts] (name ~~ "kaka" or address ~~ "kaka")' );
	}

	protected function run_test( $query, $expect ) {
		$result = $this->controller->queryToLSFilter( $query );
		$this->ok_eq( $result, $expect, "SearchFilter query '$query' doesn't match expected result." );
	}
}
