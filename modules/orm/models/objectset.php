<?php

require_once( dirname(__FILE__).'/base/baseobjectset.php' );

abstract class ObjectSet_Model extends BaseObjectSet_Model {
	public function validate_columns( $columns ) {
		$columns = parent::validate_columns($columns);
		
		$classname = $this->class;
		foreach( $classname::$macros as $match => $field ) {
			$columns[] = $field;
		}
		$this->do_column_rewrite($columns, 'current_user', array());
		return $columns;
	}
	
	protected function do_column_rewrite( &$columns, $fetched, $rewrites ) {
		if( in_array( $fetched, $columns ) ) {
			$columns = array_filter( $columns, function($row) use($fetched) {return $row != $fetched;} );
			foreach($rewrites as $rewrite) {
				$columns[] = $rewrite;
			}
		}
	}
	
	public function get_totals() {
		return array('count' => array($this->get_query(), count($this)));
	}
	
	public function get_query() {
		return '['.$this->table.'] '.$this->filter->visit(new LSFilterQueryBuilderVisitor(), 0);
	}
}
