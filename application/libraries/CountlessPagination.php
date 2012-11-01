<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * A Pagination library that doesn't require you to count() millions of rows
 */
class CountlessPagination extends Pagination_Core {
	public function initialize($config = array()) {
		$this->current_page = isset($_GET[$this->query_string]) ? (int) $_GET[$this->query_string] : 1;
		if( isset( $config['items_per_page'] ) ) {
			$this->items_per_page = $config['items_per_page'];
		}
		$config['total_items'] = ($this->current_page + 1) * $this->items_per_page;
		parent::initialize($config);
	}
}
