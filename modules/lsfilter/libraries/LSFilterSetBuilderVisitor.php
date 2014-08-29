<?php

/**
 * Used within the parser as a parse visitor of a lsfilter to generate a ORM object set
 */
class LSFilterSetBuilderVisitor extends LSFilterVisitor {
	private $metadata;
	private $pool;
	private $disabled_saved_filters = array();


	/**
	 * Generate the visitor
	 */
	public function __construct( $metadata, $disabled_saved_filters=array() ) {
		$this->metadata = $metadata;
		$this->pool = ObjectPool_Model::pool($this->metadata['name']);
		$this->all_set = $this->pool->all();
		$this->disabled_saved_filters = $disabled_saved_filters;
	}

	/**
	 * Accept the result of the parse
	 */
	public function accept($result) {
		return $result;
	}

	// entry: program := * query end
	/**
	 * Visit the given grammar rule
	 */
	public function visit_entry($query0) {
		return $query0;
	}

	// query: query := * brace_l table_def brace_r search_query
	/**
	 * Visit the given grammar rule
	 */
	public function visit_query($table_def1, $search_query3) {
		return $search_query3;
	}

	// table_def_simple: table_def := * name
	/**
	 * Visit the given grammar rule
	 */
	public function visit_table_def_simple($name0) {return null;}

	// table_def_columns: table_def := * name colon column_list
	/**
	 * Visit the given grammar rule
	 */
	public function visit_table_def_columns($name0, $column_list2) {return null;}

	// column_list_end: column_list := * name
	/**
	 * Visit the given grammar rule
	 */
	public function visit_column_list_end($name0) {return null;}

	// column_list_cont: column_list := * column_list comma name
	/**
	 * Visit the given grammar rule
	 */
	public function visit_column_list_cont($column_list0, $name2) {return null;}

	// search_query: search_query := * filter
	/**
	 * Visit the given grammar rule
	 */
	public function visit_search_query($filter0) {
		return $filter0;
	}

	// filter_or: filter := * filter or filter2
	/**
	 * Visit the given grammar rule
	 */
	public function visit_filter_or($filter0, $filter2) {
		return $filter0->union($filter2);
	}

	// filter_and: filter2 := * filter2 and filter3
	/**
	 * Visit the given grammar rule
	 */
	public function visit_filter_and($filter0, $filter2) {
		return $filter0->intersect($filter2);
	}

	// filter_not: filter3 := * not filter4
	/**
	 * Visit the given grammar rule
	 */
	public function visit_filter_not($filter1) {
		return $filter1->complement();
	}

	// filter_ok: filter4 := * match
	/**
	 * Visit the given grammar rule
	 */
	public function visit_filter_ok($match0) {
		return $match0;
	}

	// match_all: match := * all
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_all() {
		return $this->pool->all();
	}

	// match_in: match := * in string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_in($set_descr1) {
		return $this->pool->get_by_name($set_descr1, $this->disabled_saved_filters);
	}

	// match_field_in: match := * name in string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_field_in($field0, $set_descr2) {
		$table = $this->pool->get_table_for_field($field0);
		if( $table === false ) return null;

		$pool = ObjectPool_Model::pool($table);

		$set = $pool->get_by_name($set_descr2);
		if( $set === false ) return null;

		return $set->convert_to_object( $this->metadata['name'], $field0 );
	}

	// match_not_re_ci: match := * name not_re_ci arg_string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_not_re_ci($field0, $arg_string2) {
		return $this->all_set->reduce_by( $field0, $arg_string2, '!~~' );
	}

	// match_not_re_cs: match := * name not_re_cs arg_string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_not_re_cs($field0, $arg_string2) {
		return $this->all_set->reduce_by( $field0, $arg_string2, '!~' );
	}

	// match_re_ci: match := * name re_ci arg_string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_re_ci($field0, $arg_string2) {
		return $this->all_set->reduce_by( $field0, $arg_string2, '~~' );
	}

	// match_re_cs: match := * name re_cs arg_string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_re_cs($field0, $arg_string2) {
		return $this->all_set->reduce_by( $field0, $arg_string2, '~' );
	}

	// match_not_eq_ci: match := * name not_eq_ci arg_string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_not_eq_ci($field0, $arg_string2) {
		return $this->all_set->reduce_by( $field0, $arg_string2, '!=~' );
	}

	// match_eq_ci: match := * name eq_ci arg_string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_eq_ci($field0, $arg_string2) {
		return $this->all_set->reduce_by( $field0, $arg_string2, '=~' );
	}

	// match_not_eq: match := * name not_eq arg_num
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_not_eq($field0, $arg_num2) {
		return $this->all_set->reduce_by( $field0, $arg_num2, '!=' );
	}

	// match_gt_eq: match := * name gt_eq arg_num
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_gt_eq($field0, $arg_num2) {
		return $this->all_set->reduce_by( $field0, $arg_num2, '>=' );
	}

	// match_lt_eq: match := * name lt_eq arg_num
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_lt_eq($field0, $arg_num2) {
		return $this->all_set->reduce_by( $field0, $arg_num2, '<=' );
	}

	// match_gt: match := * name gt arg_num
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_gt($field0, $arg_num2) {
		return $this->all_set->reduce_by( $field0, $arg_num2, '>' );
	}

	// match_lt: match := * name lt arg_num
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_lt($field0, $arg_num2) {
		return $this->all_set->reduce_by( $field0, $arg_num2, '<' );
	}

	// match_eq: match := * name eq arg_num_string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_match_eq($field0, $arg_num_string2) {
		return $this->all_set->reduce_by( $field0, $arg_num_string2, '=' );
	}

	// set_descr_name: Êset_descr := * string
	/**
	 * Visit the given grammar rule
	 */
	public function visit_set_descr_name($string0) {
		return $string0;
	}

	// field_name: field := * name
	/**
	 * Visit the given grammar rule
	 */
	public function visit_field_name($name0) {
		return $name0;
	}
	// field_obj: field := * name dot field
	/**
	 * Visit the given grammar rule
	 */
	public function visit_field_obj($name0, $field2) {
		return $name0.".".$field2;
	}

	public function visit_arg_num_func($name0, $arg_list2)
	{
		switch ($name0) {
			case 'date':
				$ret = strtotime($arg_list2[0]);
				if (false === $ret) {
					throw new ORMException("Don't know how to translate \"" . htmlspecialchars($arg_list2[0]) . "\" into a date, please <a href=\"https://kb.op5.com/x/jIWX\" target=\"blank\">click here</a> for information on supported date formats.");
				}
				return $ret;
				break;
			default:
				return null;
		}
	}

	public function visit_arg_list($arg_num_string0, $arg_list2)
	{
		array_unshift($arg_list2, $arg_num_string0);
		return $arg_num_string0;
	}

	public function visit_arg_list_end($arg_num_string0)
	{
		return array($arg_num_string0);
	}
}
