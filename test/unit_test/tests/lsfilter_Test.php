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

	public function test_not_and() {
		$this->run_test_query(
			'[hosts] not (state=0 and state=1)',
			'[hosts] not (state=0 and state=1)'
		);
	}

	public function test_not_and_left() {
		$this->run_test_query(
			'[hosts] (not state=0) and state=1',
			'[hosts] not state=0 and state=1'
		);
	}

	public function test_not_and_left_nopar() {
		$this->run_test_query('[hosts] not state=0 and state=1');
	}

	public function test_not_and_right() {
		$this->run_test_query(
			'[hosts] not state=0 and (not state=1)',
			'[hosts] not state=0 and not state=1'
		);
	}

	public function test_not_and_right_nopar() {
		$this->run_test_query('[hosts] state=0 and not state=1');
	}

	public function test_not_or() {
		$this->run_test_query(
			'[hosts] not (state=0 or state=1)',
			'[hosts] not (state=0 or state=1)'
		);
	}

	public function test_not_or_left() {
		$this->run_test_query(
			'[hosts] (not state=0) or state=1',
			'[hosts] not state=0 or state=1'
		);
	}

	public function test_not_or_left_nopar() {
		$this->run_test_query('[hosts] not state=0 or state=1');
	}

	public function test_not_or_right() {
		$this->run_test_query(
			'[hosts] not state=0 or (not state=1)',
			'[hosts] not state=0 or not state=1'
		);
	}

	public function test_not_or_right_nopar() {
		$this->run_test_query('[hosts] state=0 or not state=1');
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
	 * Test generation of SQL query
	 */
	public function test_sql_simple_all() {
		$this->run_visitor(
			"[notifications] all",
			new LivestatusSQLBuilderVisitor(),
			"(1=1)"
			);
	}
	public function test_sql_op_eq_str() {
		$this->run_visitor(
			"[notifications] contact_name=\"kaka\"",
			new LivestatusSQLBuilderVisitor(),
			"((contact_name = 'kaka'))"
			);
	}
	public function test_sql_op_regexp_str() {
		$this->run_visitor(
			"[notifications] contact_name~~\"kaka\"",
			new LivestatusSQLBuilderVisitor(),
			"((contact_name REGEXP 'kaka'))"
			);
	}
	public function test_sql_op_not_regexp_str() {
		$this->run_visitor(
			"[notifications] contact_name!~~\"kaka\"",
			new LivestatusSQLBuilderVisitor(),
			"(NOT (contact_name REGEXP 'kaka'))"
			);
	}
	public function test_sql_op_eq_integer() {
		$this->run_visitor(
			"[notifications] id=3",
			new LivestatusSQLBuilderVisitor(),
			"((id = '3'))" /* FIXME: should be integer, but works as this */
			);
	}
	public function test_sql_andor() {
		$this->run_visitor(
			"[notifications] output=\"a\" and output=\"b\" or output=\"c\" and output=\"d\"",
			new LivestatusSQLBuilderVisitor(),
			"(((output = 'a') AND (output = 'b')) OR ((output = 'c') AND (output = 'd')))"
			);
	}
	public function test_sql_orand() {
		$this->run_visitor(
			"[notifications] (output=\"a\" or output=\"b\") and (output=\"c\" or output=\"d\")",
			new LivestatusSQLBuilderVisitor(),
			"((((output = 'a')) OR ((output = 'b'))) AND (((output = 'c')) OR ((output = 'd'))))"
			);
	}


	/*
	 * Test generation of Livestaus query
	*/
	public function test_ls_all() {
		$this->run_visitor(
				"[hosts] all",
				new LivestatusFilterBuilderVisitor(),
				array(
						"And: 0"
						)
		);
	}
	public function test_ls_not_all() {
		$this->run_visitor(
				"[hosts] not all",
				new LivestatusFilterBuilderVisitor(),
				array(
						"And: 0",
						"Negate:"
						)
		);
	}
	public function test_ls_simple_and() {
		$this->run_visitor(
				'[hosts] name="a" and name="b"',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"And: 2"
						)
		);
	}
	public function test_ls_simple_or() {
		$this->run_visitor(
				'[hosts] name="a" or name="b"',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"Or: 2"
						)
		);
	}
	public function test_ls_andor() {
		$this->run_visitor(
				'[hosts] (name="a" or name="b") and (name="c" or name="d")',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"Or: 2",
						"Filter: name = c",
						"Filter: name = d",
						"Or: 2",
						"And: 2"
						)
		);
	}
	public function test_ls_orand() {
		$this->run_visitor(
				'[hosts] (name="a" and name="b") or (name="c" and name="d")',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"And: 2",
						"Filter: name = c",
						"Filter: name = d",
						"And: 2",
						"Or: 2"
						)
		);
	}
	public function test_ls_orandnot() {
		$this->run_visitor(
				'[hosts] (name="a" and name="b") or not (name="c" and name="d")',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"And: 2",
						"Filter: name = c",
						"Filter: name = d",
						"And: 2",
						"Negate:",
						"Or: 2"
						)
		);
	}
	public function test_ls_trippleand_l() {
		$this->run_visitor(
				'[hosts] (name="a" and name="b") and name="c"',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"Filter: name = c",
						"And: 3"
						)
		);
	}
	public function test_ls_trippleand_r() {
		$this->run_visitor(
				'[hosts] name="a" and (name="b" and name="c")',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"Filter: name = c",
						"And: 3"
						)
		);
	}
	public function test_ls_trippleor_l() {
		$this->run_visitor(
				'[hosts] (name="a" or name="b") or name="c"',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"Filter: name = c",
						"Or: 3"
						)
		);
	}
	public function test_ls_trippleor_r() {
		$this->run_visitor(
				'[hosts] name="a" or (name="b" or name="c")',
				new LivestatusFilterBuilderVisitor(),
				array(
						"Filter: name = a",
						"Filter: name = b",
						"Filter: name = c",
						"Or: 3"
						)
		);
	}
	public function test_lsstats_orandnot() {
		$this->run_visitor(
				'[hosts] (name="a" and name="b") or not (name="c" and name="d")',
				new LivestatusStatsBuilderVisitor(),
				array(
						"Stats: name = a",
						"Stats: name = b",
						"StatsAnd: 2",
						"Stats: name = c",
						"Stats: name = d",
						"StatsAnd: 2",
						"StatsNegate:",
						"StatsOr: 2"
						)
		);
	}


	/*
	 * Internal methods to run a test
	*/

	private function run_visitor( $query, $visitor, $goal ) {
		if(is_array($goal)) {
			$goal = implode("\n",$goal)."\n";
		}
		$set = ObjectPool_Model::get_by_query($query);
		$converted = $set->test_visit_filter($visitor, false);
		$this->ok_eq($converted, $goal, 'Query missmatch');
	}

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