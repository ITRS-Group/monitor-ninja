<?php
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
*/
class LSFilter_Test extends PHPUnit_Framework_TestCase {

	/*
	 * Test generation of queries...
	*
	* (If they don't pass, later tests with parsing won't pass either)
	*/
	public function test_host_all_gen_query() {
		$set = HostPool_Model::all();
		$this->assertEquals($set->get_query(), '[hosts] all', 'Query missmatch');
	}

	public function test_host_filter_host_name_gen_query() {
		$set = HostPool_Model::all();
		$set = $set->reduce_by('name', 'kaka', '=');
		$this->assertEquals($set->get_query(), '[hosts] name="kaka"', 'Query missmatch');
	}

	public function test_equivalent_filter_hash () {

		$filter_a = new LivestatusFilterAnd();
		$filter_b = new LivestatusFilterAnd();

		$filter_a->add(new LivestatusFilterMatch('type', 'type', '='));
		$filter_b->add(new LivestatusFilterMatch('type', 'type', '='));

		$this->assertEquals($filter_a->get_hash(), $filter_b->get_hash(), "Filter hashes do not match");
	}

	public function test_host_filter_host_nested_and_or() {
		$all_set = HostPool_Model::all();

		$or_set = $all_set->reduce_by('state', 0, '=');
		$or_set = $or_set->union($all_set->reduce_by('state', 1, '='));

		$and_set = $all_set;
		$and_set = $and_set->reduce_by('name', 'kaka', '=');
		$and_set = $and_set->intersect($or_set);

		$set = $and_set->union($all_set->reduce_by('plugin_output', 'show_me', '~~'));

		$this->assertEquals($set->get_query(), '[hosts] name="kaka" and (state=0 or state=1) or plugin_output~~"show_me"', 'Query missmatch');
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
		$this->run_test_query('[hosts] all or all', '[hosts] all');
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
				'[hosts] state!=0 or state!=1'
		);
	}

	public function test_not_and_left() {
		$this->run_test_query(
				'[hosts] (not state=0) and state=1',
				'[hosts] state!=0 and state=1'
		);
	}

	public function test_not_and_left_nopar() {
		$this->run_test_query('[hosts] not state=0 and state=1', '[hosts] state!=0 and state=1');
	}

	public function test_not_and_right() {
		$this->run_test_query(
				'[hosts] not state=0 and (not state=1)',
				'[hosts] state!=0 and state!=1'
		);
	}

	public function test_not_and_right_nopar() {
		$this->run_test_query('[hosts] state=0 and not state=1', '[hosts] state=0 and state!=1');
	}

	public function test_not_or() {
		$this->run_test_query(
				'[hosts] not (state=0 or state=1)',
				'[hosts] state!=0 and state!=1'
		);
	}

	public function test_not_or_left() {
		$this->run_test_query(
				'[hosts] (not state=0) or state=1',
				'[hosts] state!=0 or state=1'
		);
	}

	public function test_not_or_left_nopar() {
		$this->run_test_query('[hosts] not state=0 or state=1', '[hosts] state!=0 or state=1');
	}

	public function test_not_or_right() {
		$this->run_test_query(
				'[hosts] not state=0 or (not state=1)',
				'[hosts] state!=0 or state!=1'
		);
	}

	public function test_not_or_right_nopar() {
		$this->run_test_query('[hosts] state=0 or not state=1', '[hosts] state=0 or state!=1');
	}

	/*
	 * Test parse fail
	 */
	/**
	 * @expectedException LSFilterException
	 */
	public function test_missing_end_parentisis() {
		ObjectPool_Model::get_by_query('[hosts] (all');
	}

	/**
	 * @expectedException LSFilterException
	 */
	public function test_extra_end_parentisis() {
		$set = ObjectPool_Model::get_by_query('[hosts] (all))');
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

	/**
	 * @expectedException ORMException
	 */
	public function test_nonexisting_tables() {
		ObjectPool_Model::get_by_query("[nonexisting] all");
	}


	/*
	 * Test generation of SQL query
	*/
	public function test_sql_simple_all() {
		$this->run_visitor(
				"[notifications] all",
				new LivestatusSQLBuilderVisitor(function($column) {return $column;}),
				"(1=1)"
		);
	}
	public function test_sql_andor() {
		$this->run_visitor(
				"[notifications] output=\"a\" and output=\"b\" or output=\"c\" and output=\"d\"",
				new LivestatusSQLBuilderVisitor(function($column) {return $column;}),
				"(((output = 'a') AND (output = 'b')) OR ((output = 'c') AND (output = 'd')))"
		);
	}
	public function test_sql_orand() {
		$this->run_visitor(
				"[notifications] (output=\"a\" or output=\"b\") and (output=\"c\" or output=\"d\")",
				new LivestatusSQLBuilderVisitor(function($column) {return $column;}),
				"((((output = 'a')) OR ((output = 'b'))) AND (((output = 'c')) OR ((output = 'd'))))"
		);
	}

	private function do_test_sql_op_str($op, $result) {
		$this->run_visitor(
				"[notifications] output $op \"a\"",
				new LivestatusSQLBuilderVisitor(function($column) {return $column;}),
				$result
		);
	}
	private function do_test_sql_op_int($op, $result) {
		$this->run_visitor(
				"[hosts] state $op 2",
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
				array( "Filter: state $op 2" )
		);
	}

	//	not_re_ci   /^(!~~)/
	public function test_sql_op_not_re_ci_str() {
		$this->do_test_sql_op_str('!~~',"(NOT (output REGEXP 'a'))");
	}

	//	not_re_cs   /^(!~)/
	public function test_sql_op_not_re_cs_str() {
		$this->do_test_sql_op_str('!~',"(NOT (output REGEXP BINARY 'a'))");
	}
	//	re_ci       /^(~~)/
	public function test_sql_op_re_ci_str() {
		$this->do_test_sql_op_str('~~',"((output REGEXP 'a'))");
	}

	//	re_cs       /^(~)/
	public function test_sql_op_re_cs_str() {
		$this->do_test_sql_op_str('~',"((output REGEXP BINARY 'a'))");
	}

	//	not_eq_ci   /^(!=~)/
	public function test_sql_op_not_eq_ci_str() {
		$this->do_test_sql_op_str('!=~',"((output != 'a'))");
	}

	//	eq_ci       /^(=~)/
	public function test_sql_op_eq_ci_str() {
		$this->do_test_sql_op_str('=~',"((output = 'a'))");
		/* FIXME: don't care about case right now */
	}

	//	not_eq      /^(!=)/
	public function test_sql_op_not_eq_str() {
		$this->do_test_sql_op_str('!=',"((output != 'a'))");
		/* FIXME: don't care about case right now */
	}
	public function test_sql_op_not_eq() {
		$this->do_test_sql_op_int('!=',"((output != '3'))");
		/* FIXME: Is integer */
	}

	//	gt_eq       /^(>=)/
	public function test_sql_op_gt_eq() {
		$this->do_test_sql_op_int('>=',"((output >= '3'))");
		/* FIXME: Is integer */
	}
	/*
	FIXME: This test is incorrect, but not relevant.
	>= is an operatior which means "contains", which isn't avalible in
	SQL tables.

	It can be used to actually do range operators on strings ATM, but isn't
	designed to do that...

	public function test_sql_op_gt_eq_str() {
		$this->do_test_sql_op_str('>=',"((output >= 'a'))");
	}
	*/

	//	lt_eq       /^(<=)/
	public function test_sql_op_lt_eq() {
		$this->do_test_sql_op_int('<=',"((output <= '3'))");
		/* FIXME: Is integer */
	}

	//	gt          /^(>)/
	public function test_sql_op_gt() {
		$this->do_test_sql_op_int('>',"((output > '3'))");
		/* FIXME: Is integer */
	}

	//	lt          /^(<)/
	public function test_sql_op_lt() {
		$this->do_test_sql_op_int('<',"((output < '3'))");
		/* FIXME: Is integer */
	}

	//	eq          /^(=)/
	public function test_sql_op_eq_str() {
		$this->do_test_sql_op_str('=',"((output = 'a'))");
		/* FIXME: don't care about case right now */
	}
	public function test_sql_op_eq() {
		$this->do_test_sql_op_int('=',"((output = '3'))");
		/* FIXME: Is integer */
	}




	/*
	 * Test generation of Livestaus query
	*/
	public function test_ls_all() {
		$this->run_visitor(
				"[hosts] all",
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
				array(
						"And: 0"
				)
		);
	}
	public function test_ls_not_all() {
		$this->run_visitor(
				"[hosts] not all",
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
				array(
						"And: 0",
						"Negate:"
				)
		);
	}
	public function test_ls_simple_and() {
		$this->run_visitor(
				'[hosts] name="a" and name="b"',
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
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
				new LivestatusStatsBuilderVisitor(function($column) {return $column;}),
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

	private function do_test_ls_op_str($op) {
		$this->run_visitor(
				"[hosts] name $op \"a\"",
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
				array( "Filter: name $op a" )
		);
	}
	private function do_test_ls_op_int($op) {
		$this->run_visitor(
				"[hosts] state $op 2",
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
				array( "Filter: state $op 2" )
		);
	}

	//	not_re_ci   /^(!~~)/
	public function test_ls_op_not_re_ci_str() {
		$this->do_test_ls_op_str("!~~");
	}

	//	not_re_cs   /^(!~)/
	public function test_ls_op_not_re_cs_str() {
		$this->do_test_ls_op_str("!~");
	}
	//	re_ci       /^(~~)/
	public function test_ls_op_re_ci_str() {
		$this->do_test_ls_op_str("~~");
	}

	//	re_cs       /^(~)/
	public function test_ls_op_re_cs_str() {
		$this->do_test_ls_op_str("~");
	}

	//	not_eq_ci   /^(!=~)/
	public function test_ls_op_not_eq_ci_str() {
		$this->do_test_ls_op_str("!=~");
	}

	//	eq_ci       /^(=~)/
	public function test_ls_op_eq_ci_str() {
		$this->do_test_ls_op_str("=~");
	}

	//	not_eq      /^(!=)/
	public function test_ls_op_not_eq_str() {
		$this->do_test_ls_op_str("!=");
	}
	public function test_ls_op_not_eq() {
		$this->do_test_ls_op_int("!=");
	}

	//	gt_eq       /^(>=)/
	public function test_ls_op_gt_eq() {
		$this->do_test_ls_op_int(">=");
	}
	public function test_ls_op_gt_eq_str() {
		$this->do_test_ls_op_str(">=");
	}

	//	lt_eq       /^(<=)/
	public function test_ls_op_lt_eq() {
		$this->do_test_ls_op_int("<=");
	}

	//	gt          /^(>)/
	public function test_ls_op_gt() {
		$this->do_test_ls_op_int(">");
	}

	//	lt          /^(<)/
	public function test_ls_op_lt() {
		$this->do_test_ls_op_int("<");
	}

	//	eq          /^(=)/
	public function test_ls_op_eq_str() {
		$this->do_test_ls_op_str("=");
	}
	public function test_ls_op_eq() {
		$this->do_test_ls_op_int("=");
	}

	public function test_ls_op_date() {
		$this->run_visitor(
				"[hosts] last_check > date(\"1970-01-01 00:00:02 UTC\")",
				new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
				array( "Filter: last_check > 2" )
		);
	}

	// invalid/unparsable date texts should not be accepted (bug #9079)
	/**
	 * @expectedException ORMException
	 */
	public function test_ls_op_date_invalid_text() {
		$this->run_visitor(
			"[hosts] last_check = date(\"four score and seven years ago\")",
			new LivestatusFilterBuilderVisitor(function($column) {return $column;}),
			array( "I don't even ... Just odd.")
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
		$this->assertEquals($converted, $goal, 'Query missmatch');
	}

	private function run_test_query( $query, $match_query = false ) {
		if( $match_query === false )
			$match_query = $query;
		$set = ObjectPool_Model::get_by_query($query);
		$gen_query = $set->get_query();
		$this->assertEquals($match_query, $gen_query, 'Query missmatch');
	}
}
