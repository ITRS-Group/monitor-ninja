<?php

/**
 * Used within the parser as a parse visitor of a lsfilter to generate a ORM object set
 */
class LivestatusSQLBuilderVisitor implements LivestatusFilterVisitor {
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
		$field = str_replace('.','_',$filt->get_field());
		$value = $filt->get_value();
		$op = $filt->get_op();
		switch( $filt->get_op() ) {
			case '!~~':
				return "NOT ($field REGEXP $value)";
			case '!~':
				return "NOT ($field REGEXP BINARY $value)";
			case '~~':
				$op = 'REGEXP';
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
		$value = Database::instance()->escape($value);
		return "($field $op $value)";
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
