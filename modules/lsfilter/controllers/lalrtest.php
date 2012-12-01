<?php

class lalrtest_visitor {
	public function visit_expr_not( $arg ) {
		print "Call: visit_expr_not($arg)\n";
		return "!$arg";
	}
	
	public function __call( $method, $args ) {
		print "Call: $method(".implode( ', ', $args ).")\n";
		return implode(', ',$args);
	}
}

class testvisit extends LSFilterVisitor_Core {
	// entry: program := * query end
	public function visit_entry($query0) {}

	// query: query := * brace_l table_def brace_r search_query
	public function visit_query($table_def1, $search_query3) {
		$table_def1[] = $search_query3;
		return $table_def1;
	}

	// table_def_simple: table_def := * name
	public function visit_table_def_simple($name0) {
		return array( $name0, array() );
	}

	// table_def_columns: table_def := * name colon column_list
	public function visit_table_def_columns($name0, $column_list2) {
		return array( $name0, $column_list2 );
	}

	// column_list_end: column_list := * name
	public function visit_column_list_end($name0) {
		return array( $name0 );
	}

	// column_list_cont: column_list := * column_list comma name
	public function visit_column_list_cont($column_list0, $name2) {
		$column_list0[] = $name2;
		return $column_list0;
	}

	// search_query: search_query := * expr
	public function visit_search_query($expr0) {
		return $expr0;
	}

	// expr_add: expr := * expr2 op_add expr
	public function visit_expr_add($expr20, $expr2) {
		return $expr20 + $expr2;
	}

	// expr_sub: expr := * expr2 ap_sub expr
	public function visit_expr_sub($expr20, $expr2) {
		return $expr20 - $expr2;
	}

	// expr_end: expr := * expr2
	public function visit_expr_end($expr20) {
		return $expr20;
	}

	// expr_mult: expr2 := * expr3 op_mult expr2
	public function visit_expr_mult($expr30, $expr22) {
		return $expr30*$expr22;
	}

	// expr_div: expr2 := * expr3 op_div expr2
	public function visit_expr_div($expr30, $expr22) {
		return $expr30/$expr22;
	}

	// expr_end2: expr2 := * expr3
	public function visit_expr_end2($expr30) {
		return $expr30;
	}

	// expr_par: expr3 := * par_l expr par_r
	public function visit_expr_par($expr1) {
		return $expr1;
	}

	// expr_num: expr3 := * integer
	public function visit_expr_num($integer0) {
		return $integer0;
	}

	// expr_name: expr3 := * name
	public function visit_expr_name($name0) {
		print "Got name: $name0, assuming 0\n";
	}

	public function accept($result) {
		return $result;
	}

}

class lalrtest_Controller extends Ninja_Controller {
	public function index() {
		$string = $GLOBALS['argv'][2];
		try {
			print "Parsing: $string\n";
			
			$parser = new LSFilter( new LSFilterPreprocessor_Core(), new testvisit() );
			print_r( $parser->parse( $string ) );
			
		} catch( Exception $e ) {
			print "Exception: ".$e->getMessage()."\n\n";
		}
		die();
	}
}