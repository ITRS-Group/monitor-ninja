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
		$this->run_test('h:kaka', array('hosts'=>'[hosts] (name ~~ "kaka" or display_name ~~ "kaka" or address ~~ "kaka" or alias ~~ "kaka")') );
	}
	public function test_service() {
		$this->run_test('s:kaka', array('services'=>'[services] (description ~~ "kaka" or display_name ~~ "kaka")') );
	}
	public function test_hostgroups() {
		$this->run_test('hg:kaka', array('hostgroups'=>'[hostgroups] (name ~~ "kaka" or alias ~~ "kaka")') );
	}
	public function test_servicegroups() {
		$this->run_test('sg:kaka', array('servicegroups'=>'[servicegroups] (name ~~ "kaka" or alias ~~ "kaka")') );
	}
	public function test_status_info() {
		$this->run_test('si:kaka', array('hosts'=>'[hosts] (plugin_output ~~ "kaka")','services'=>'[services] (plugin_output ~~ "kaka")') );
	}
	
	/* ******
	 * Test wildcard search
	 */
	public function test_wildcard() {
		$this->run_test('h:aaa%bbb', array('hosts'=>'[hosts] (name ~~ "aaa.*bbb" or address ~~ "aaa.*bbb" or display_name ~~ "aaa.*bbb" or alias ~~ "aaa.*bbb")') );
	}
	
	
	/* ******
	 * Test combined host/service (services by hosts)
	 */
	public function test_host_serivce() {
		$this->run_test('h:kaka and s:pong', array('services'=>'[services] (description ~~ "pong" or display_name ~~ "pong") and (host.name ~~ "kaka" or host.display_name ~~ "kaka" or host.address ~~ "kaka" or host.alias ~~ "kaka")') );
	}
	
	/* ******
	 * Test limit
	 */
	public function test_host_limit() {
		$this->run_test('h:kaka limit=24', array('hosts'=>'[hosts] (name ~~ "kaka" or display_name ~~ "kaka" or address ~~ "kaka" or alias ~~ "kaka")', 'limit'=>24) );
	}

	protected function run_test( $query, $expect ) {
		$result = $this->controller->queryToLSFilter( $query );
		$this->ok_eq( $result, $expect, "SearchFilter query '$query' doesn't match expected result." );
	}
}
