<?php
/**
 * Visitor which translates a given filter to an equivalent native PHP
 * filter.
 */
class NativeFilterBuilderVisitor implements LivestatusFilterVisitor {
	/**
	 * Visit an and node
	 */
	public function visit_and( LivestatusFilterAnd $filt, $data ) {
		foreach ( $filt->get_sub_filters() as $subfilter) {
			if (!$subfilter->visit($this, $data)) {
				return false;
			}
		}
		return true;
  }

	/**
	 * Visit an or node
	 */
	public function visit_or( LivestatusFilterOr $filt, $data ) {
		foreach ( $filt->get_sub_filters() as $subfilter) {
			if ($subfilter->visit($this, $data)) {
				return true;
			}
		}
		return false;
  }

	/**
	 * Visit an value match node
	 */
	public function visit_match( LivestatusFilterMatch $filt, $data ) {
		$field = $filt->get_field();
		$lhs = call_user_func(array( $data->class_pool(), 'map_name_to_backend'), $field);
		$value = $filt->get_value();
		$op = $filt->get_op();
		if( empty($value) ) {
			/* Special case on empty valued regexp */
			switch( $op ) {
				case '!~~':
				case '!~':
					return false; /* Matches nothing */
				case '~~':
				case '~':
					return true; /* Matches everything */
			}
			/* Otherwise drop through */
		}

		switch( $op ) {
			case '!~~':
				return !preg_match("/" . $value . "/i", $lhs);
			case '!~':
				return !preg_match("/" . $value . "/", $lhs);
			case '~~':
				return preg_match("/" . $value . "/i", $lhs);
			case '~':
				return preg_match("/" . $value . "/", $lhs);
			case '!=~':
				return strtolower($lhs) != strtolower($value);
			case '=~':
				return strtolower($lhs) == strtolower($value);
			case '=':
				return $lhs == $value;
			case '!=':
				return $lhs != $value;
			case '>=':
				return $lhs >= $value;
			case '<=':
				return $lhs <= $value;
			case '>':
				return $lhs > $value;
			case '<':
				return $lhs < $value;
		}

		throw new ORMException("Unknown binary operator '$op'!");
	}

	/**
	 * Visit a negation node
	 */
	public function visit_not( LivestatusFilterNot $filt, $data ) {
		return !$filt->get_filter()->visit($this, $data);
	}

}
