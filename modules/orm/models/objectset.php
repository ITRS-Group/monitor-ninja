<?php


abstract class ObjectSet_Model extends BaseObjectSet_Model {
	public function get_totals() {
		return array('count' => array($this->get_query(), count($this)));
	}
	
	public function get_query() {
		return '['.$this->table.'] '.$this->filter->visit(new LSFilterQueryBuilderVisitor(), 0);
	}
}
