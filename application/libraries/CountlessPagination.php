<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * A Pagination library that doesn't require you to count() millions of rows
 */
class CountlessPagination extends Pagination_Core {
	public function initialize($config = array()) {
		if( isset( $config['query_string'] ) ) {
			$this->query_string = $config['query_string'];
		} else {
			$this->query_string = 'page'; /* Ehum... TODO? This is ugly... */
		}

		$this->current_page = isset($_GET[$this->query_string]) ? (int) $_GET[$this->query_string] : 1;
		if( isset( $config['items_per_page'] ) ) {
			$this->items_per_page = $config['items_per_page'];
		}
		if(!isset($config['style'])) {
			$config['style'] = 'digg-pageless';
		}
		$config['total_items'] = ($this->current_page + 1) * $this->items_per_page;
		parent::initialize($config);
		if (isset($config['extra_params'])) {
			$base_url = ($this->base_url === '') ? Router::$current_uri : $this->base_url;
			$this->url = url::site($base_url).'?'.$config['extra_params'].'&amp;page={page}';
		}
	}
}
