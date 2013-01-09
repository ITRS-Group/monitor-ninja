<?php

require_once( dirname(__FILE__).'/base/baseobjectset.php' );

abstract class ObjectSet_Model extends BaseObjectSet_Model {
	public function validate_columns( $columns ) {
		
		$classname = $this->class;
		foreach( $classname::$macros as $match => $field ) {
			$columns[] = $field;
		}
		$columns = array_filter($columns,function($row) { return $row != 'current_user'; });
		return parent::validate_columns($columns);
	}
	
	public function get_totals() {
		return array('count' => array($this->get_query(), count($this)));
	}
	
	public function get_query() {
		return '['.$this->table.'] '.$this->filter->visit(new LSFilterQueryBuilderVisitor(), 0);
	}
}
