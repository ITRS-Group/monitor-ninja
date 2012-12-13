<?php


abstract class ObjectSet_Model extends BaseObjectSet_Model {
	public function get_totals() {
		return array('count' => $this->count());
	}
	
	public function get_query() {
		return '['.$this->table.'] '.$this->filter->visit(new LSFilterQueryBuilderVisitor(), 0);
	}
}
