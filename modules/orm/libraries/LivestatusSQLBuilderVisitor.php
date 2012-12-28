<?php

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

	public function visit_and( LivestatusFilterAnd $filt, $data ) {
		return $this->visit_op( $filt, $data, 'AND', '1=1' );
	}

	public function visit_or( LivestatusFilterOr $filt, $data ) {
		return $this->visit_op( $filt, $data, 'OR', '1=0' );
	}

	public function visit_match( LivestatusFilterMatch $filt, $data ) {
		$field = str_replace('.','_',$filt->get_field());
		$value = Database::instance()->escape($filt->get_value());
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

			case '!=':
			case '>=':
			case '<=':
			case '>':
			case '<':
			case '=':
				break;
		}
		return "($field $op $value)";
	}

	public function visit_not( LivestatusFilterNot $filt, $data ) {
		$subfilter = $filt->get_filter();
		$result = $subfilter->visit($this,false);
		return "(NOT $result)";
	}
}
