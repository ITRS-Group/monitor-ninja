<?php

require_once( dirname(__FILE__).'/base/baseobjectset.php' );

/**
 * Describes a set of objects from livestatus
 */
abstract class ObjectSet_Model extends BaseObjectSet_Model {
	/**
	 * Valideate an array of columns for the given table
	 */
	public function validate_columns( $columns ) {
		
		$classname = $this->class;
		foreach( $classname::$macros as $match => $field ) {
			$columns[] = $field;
		}
		$columns = array_filter($columns,function($row) { return $row != 'current_user'; });
		return parent::validate_columns($columns);
	}
	
	/**
	 * Get statistics about the set, by default only the count
	 */
	public function get_totals() {
		return array('count' => array($this->get_query(), count($this)));
	}
	
	/**
	 * Get the query representing the set
	 */
	public function get_query() {
		return '['.$this->table.'] '.$this->filter->visit(new LSFilterQueryBuilderVisitor(), 0);
	}
	
	/**
	 * Get the first matching object in the set
	 */
	public function one($columns = false) {
		return $this->it($columns, array(),1,0)->current();
	}
}
