<?php

/**
 * Used within the parser as a parse visitor of a lsfilter to generate a ORM object set
 */
class LivestatusSQLBuilderVisitor implements LivestatusFilterVisitor {
	/** a callback for converting a ORM layer column name to a database layer column name */
	protected $column_formatter;

	/**
	 * Create new sql visitor
	 * @param $column_formatter a callback for converting a ORM layer column name to a database layer column name
	 */
	public function __construct($column_formatter) {
		$this->column_formatter = $column_formatter;
	}
	private function visit_op( $filt, $data, $op, $default ) {
		$subfilters = $filt->get_sub_filters();
		$result = array();
		foreach( $subfilters as $subf )
			$result[] = $subf->visit($this,false);
		if( count($result) == 0 ) {
			$result[] = $default;
		}
		return '('.implode(" $op ", $result).')';
	}

	/**
	 * Visit an and node
	 */
	public function visit_and( LivestatusFilterAnd $filt, $data ) {
		return $this->visit_op( $filt, $data, 'AND', '1=1' );
	}

	/**
	 * Visit an or node
	 */
	public function visit_or( LivestatusFilterOr $filt, $data ) {
		return $this->visit_op( $filt, $data, 'OR', '1=0' );
	}

	/**
	 * Visit an value match node
	 */
	public function visit_match( LivestatusFilterMatch $filt, $data ) {
		$field = $filt->get_field();
		$field = call_user_func($this->column_formatter, $field);
		$value = $filt->get_value();
		$op = $filt->get_op();
		if( empty($value) ) {
			/* Special case on empty valued regexp */
			switch( $filt->get_op() ) {
				case '!~~':
				case '!~':
					return "1=0"; /* Matches nothing */
				case '~~':
				case '~':
					return "1=1"; /* Matches everything */
			}
			/* Otherwise drop through */
		}
		$value_esc = Database::instance()->escape($value);
		switch( $filt->get_op() ) {
			case '!~~':
				return "NOT ($field REGEXP $value_esc)";
			case '!~':
				return "NOT ($field REGEXP BINARY $value_esc)";
			case '~~':
				$op = 'collate latin1_swedish_ci REGEXP';
				break;
			case '~':
				$op = 'REGEXP BINARY';
				break;

			case '!=~':
				$op = '!=';
				break;

			case '=~':
				$op = '=';
				break;

			case '=':
				if($value === null)
					return "($field IS NULL)";
			case '!=':
				if($value === null)
					return "($field IS NOT NULL)";
			case '>=':
			case '<=':
			case '>':
			case '<':
				break;
		}
		return "($field $op $value_esc)";
	}

	/**
	 * Visit an negation node
	 */
	public function visit_not( LivestatusFilterNot $filt, $data ) {
		$subfilter = $filt->get_filter();
		$result = $subfilter->visit($this,false);
		return "(NOT $result)";
	}
}
