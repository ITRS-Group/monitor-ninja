<?php
class LSFilterMetadataVisitor_Core extends LSFilterVisitor_Core {
	public function get_table() {
		return $this->table;
	}
	
	public function get_columns() {
		return $this->columns;
	}
	
	// entry: program := * query end
	public function visit_entry($query0) {
		return $query0;
	}

	// query: query := * brace_l table_def brace_r search_query
	public function visit_query($table_def1, $search_query3) {
		return $table_def1;
	}

	// table_def_simple: table_def := * name
	public function visit_table_def_simple($name0) {
		return array('name' => $name0);
	}

	// table_def_columns: table_def := * name colon column_list
	public function visit_table_def_columns($name0, $column_list2) {
		return array('name' => $name0, 'columns' => $column_list2);
	}

	// column_list_end: column_list := * name
	public function visit_column_list_end($name0) {
		return array($name0);
	}

	// column_list_cont: column_list := * column_list comma name
	public function visit_column_list_cont($column_list0, $name2) {
		$column_list0[] = $name2;
		return $column_list0;
	}

	// search_query: search_query := * filter
	public function visit_search_query($filter0) {
		return null;
	}

	// filter_or: filter := * filter or filter2
	public function visit_filter_or($filter0, $filter2) {
		return null;
	}

	// filter_and: filter2 := * filter2 and filter3
	public function visit_filter_and($filter0, $filter2) {
		return null;
	}

	// filter_not: filter3 := * not filter4
	public function visit_filter_not($filter1) {
		return null;
	}

	// filter_ok: filter4 := * match
	public function visit_filter_ok($match0) {
		return null;
	}

	// match_in: match := * in string
	public function visit_match_in($set_descr1) {
		return null;
	}

	// match_all: match := * all
	public function visit_match_all() {
		return null;
	}
	
	// match_field_in: match := * name in string
	public function visit_match_field_in($name0, $set_descr2) {
		return null;
	}

	// match_not_re_ci: match := * name not_re_ci arg_string
	public function visit_match_not_re_ci($name0, $arg_string2) {
		return null;
	}

	// match_not_re_cs: match := * name not_re_cs arg_string
	public function visit_match_not_re_cs($name0, $arg_string2) {
		return null;
	}

	// match_re_ci: match := * name re_ci arg_string
	public function visit_match_re_ci($name0, $arg_string2) {
		return null;
	}

	// match_re_cs: match := * name re_cs arg_string
	public function visit_match_re_cs($name0, $arg_string2) {
		return null;
	}

	// match_not_eq_ci: match := * name not_eq_ci arg_string
	public function visit_match_not_eq_ci($name0, $arg_string2) {
		return null;
	}

	// match_eq_ci: match := * name eq_ci arg_string
	public function visit_match_eq_ci($name0, $arg_string2) {
		return null;
	}

	// match_not_eq: match := * name not_eq arg_num
	public function visit_match_not_eq($name0, $arg_num2) {
		return null;
	}

	// match_gt_eq: match := * name gt_eq arg_num
	public function visit_match_gt_eq($name0, $arg_num2) {
		return null;
	}

	// match_lt_eq: match := * name lt_eq arg_num
	public function visit_match_lt_eq($name0, $arg_num2) {
		return null;
	}

	// match_gt: match := * name gt arg_num
	public function visit_match_gt($name0, $arg_num2) {
		return null;
	}

	// match_lt: match := * name lt arg_num
	public function visit_match_lt($name0, $arg_num2) {
		return null;
	}

	// match_eq: match := * name eq arg_num_string
	public function visit_match_eq($name0, $arg_num_string2) {
		return null;
	}
	
	// set_descr_name: Êset_descr := * string
	public function visit_set_descr_name($string0) {
		return null;
	}
	
	// set_descr_query: Êset_descr := * query
	public function visit_set_descr_query($query0) {
		return null;
	}
	
	// field_name: field := * name
	public function visit_field_name($name0) {
		return null;
	}
	
	// field_obj: field := * name dot field
	public function visit_field_obj($name0, $field2) {
		return null;
	}

	public function accept($result) {
		return $result;
	}
}