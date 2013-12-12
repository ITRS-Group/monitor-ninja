<?php

/**
 * Models the interface of saved queries for the listview search filters
 */
class LSFilter_Data_Model extends Model {

	/**
	 * @param $query string
	 * @param $limit int
	 * @param $offset int = false @TODO: should be 0..?
	 * @param $columns array = false @TODO: should be array()..?
	 * @param $sort array = array() [col1, col2, ..., colN]
	 *
	 * @throws LSFilterException
	 * @throws ORMException
	 * @throws Exception
	 */
	function query($query, $limit, $offset = false, $columns = false, array $sort = array()) {
		/* TODO: Fix sorting better sometime
		 * Do it though ORM more orm-ly
		 * Check if columns exists and so on...
		 */
		$sort = array_map(function($el){return str_replace('.','_',$el);},$sort);
		$result_set = ObjectPool_Model::get_by_query( $query );

		/*
		 * Some magic column filtering
		 *
		 * We need to strip away requested columns that isn't available.
		 * The ORM layer isn't, and shouldn't, be forgiving about columns
		 * taht doesn't exist. But due to custom columns, that (yet) doesn't
		 * know about "virtual" columns (columns defined as methods in the
		 * ORM models), we need to expect that those exist, try to request
		 * them, and then handle them as undefined if not defined in the
		 * result, instead of handling them as undefined already in the
		 * column definition.
		 */
		$structure = Module_Manifest_Model::get('orm_structure');

		/* Extract virtual columns, so we can filter against structure */
		$raw_columns = $result_set->validate_columns($columns);

		/* Check each column against structure */
		$columns = array();
		foreach($raw_columns as $column) {
			$parts = explode('.',$column);
			$table = $result_set->get_table();
			$accept = true;
			/* Columns can be object.field, so iterate over each part */
			foreach($parts as $part) {
				if( isset($structure[$table][$part]) ) {
					if($structure[$table][$part][0] == 'object') {
						$table = $structure[$table][$part][1];
					}
				} else {
					$accept = false;
				}
			}
			/* Write back columns we accept */
			if( $accept ) {
				$columns[] = $column;
			}
		}

		$data = array();
		foreach( $result_set->it($columns,$sort,$limit,$offset) as $elem ) {
			$data[] = $elem->export();
		}
		return array(
			'totals' => $result_set->get_totals(),
			'data' => $data,
			'table' => $result_set->get_table(),
			'count' => count($result_set)
		);
	}
}
