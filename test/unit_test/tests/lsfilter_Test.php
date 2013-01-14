<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
*/
class LSFilter_Test extends TapUnit {

	/*
	 * Test generation of queries...
	*
	* (If they don't pass, later tests with parsing won't pass either)
	*/
	public function test_host_all_gen_query() {
		$set = HostPool_Model::all();
		$this->ok_eq($set->get_query(), '[hosts] all', 'Query missmatch');
	}

	public function test_host_filter_host_name_gen_query() {
		$set = HostPool_Model::all();
		$set = $set->reduce_by('name', 'kaka', '=');
		$this->ok_eq($set->get_query(), '[hosts] name="kaka"', 'Query missmatch');
	}

	public function test_host_filter_host_nested_and_or() {
		$all_set = HostPool_Model::all();

		$or_set = $all_set->reduce_by('state', 0, '=');
		$or_set = $or_set->union($all_set->reduce_by('state', 1, '='));

		$and_set = $all_set;
		$and_set = $and_set->reduce_by('name', 'kaka', '=');
		$and_set = $and_set->intersect($or_set);

		$set = $and_set->union($all_set->reduce_by('plugin_output', 'show_me', '~~'));

		$this->ok_eq($set->get_query(), '[hosts] name="kaka" and (state=0 or state=1) or plugin_output~~"show_me"', 'Query missmatch');
	}

	/*
	 * Test parsing, and generation.
	*
	* Query =parse=> Set =generation=> Query
	*
	* Those tests passes through a couple of independent systems:
	* - parser (tests grammar and parser generator)
	* - ORM Pools, tests methods:
	*   - all
	*   - get_by_query
	* - ORM Sets, tests methods:
	*   - union
	*   - intersect
	*   - reduce_by
	*   - get_query
	* - Query generation, tests LSFilterQueryBuilderVisitor
	*/

	public function test_all() {
		$this->run_test_query('[hosts] all');
	}

	public function test_all_and_all() {
		$this->run_test_query('[hosts] all and all', '[hosts] all');
	}

	public function test_all_or_all() {
		$this->run_test_query('[hosts] all or all', '[hosts] all or all');
	}

	public function test_nested_simple_paranthesis() {
		$this->run_test_query(
			'[hosts] (state=0)',
			'[hosts] state=0'
		);
	}

	public function test_nested_paranthesis() {
		$this->run_test_query(
			'[hosts] ((state=0) or (state=1)) and ((state=2) and ((state=3)))',
			'[hosts] (state=0 or state=1) and state=2 and state=3'
		);
	}

	public function test_nested_andor() {
		$this->run_test_query( '[hosts] (state=0 or state=1) and (name~~"server" or name~~"router")' );
	}

	public function test_nested_orand() {
		$this->run_test_query( '[hosts] state=0 and state=1 or name~~"server" and name~~"router"' );
	}

	public function test_nested_orand_strip_par() {
		$this->run_test_query(
			'[hosts] (state=0 and state=1) or (name~~"server" and name~~"router")',
			'[hosts] state=0 and state=1 or name~~"server" and name~~"router"'
		);
	}

	/*
	 * Test parse fail
	*/

	public function test_missing_end_parentisis() {
		$this->run_parse_fail('[hosts] (all');
	}

	public function test_extra_end_parentisis() {
		$this->run_parse_fail('[hosts] (all))');
	}

	/*
	 *  Test access to tables
	*/

	public function test_tables() {
		$tables = array(
			'columns', 'commands', 'comments', 'contacts', 'contactgroups',
			'downtimes', 'hosts', 'hostgroups', 'notifications', 'services',
			'servicegroups', 'status', 'timeperiods'
		);
		foreach($tables as $table) {
			$this->run_test_query("[$table] all");
		}
	}
	
	public function test_nonexisting_tables() {
			$this->run_parse_fail("[nonexisting] all");
	}

	/*
	 * Internal methods to run a test
	*/

	private function run_test_query( $query, $match_query = false ) {
		if( $match_query === false )
			$match_query = $query;
		$set = ObjectPool_Model::get_by_query($query);
		$gen_query = $set->get_query();
		$this->ok_eq($gen_query, $match_query, 'Query missmatch');
	}

	private function run_parse_fail( $query ) {
		try {
			$set = ObjectPool_Model::get_by_query($query);
			$this->fail('Exception expected');
		} catch( Exception $e ) {
			$this->pass( 'Got exception as expected: '.$e->getMessage() );
		}
	}
}