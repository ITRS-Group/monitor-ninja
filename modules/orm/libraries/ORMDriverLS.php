<?php
/**
 * An ORM driver which is backed by Livestatus
 */
class ORMDriverLS implements ORMDriverInterface {
	public function count($table, $structure, $filter) {
		$ls = op5livestatus::instance();

		$fb_visit = new LivestatusFilterBuilderVisitor(array($structure['class'] . 'Pool_Model', "map_name_to_backend"));
		$ls_filter = $filter->visit($fb_visit, false);
		$ls_filter .= "Limit: 0\n";

		$result = $ls->query($table, $ls_filter, false);

		return $result[2];

	}

	public function it($table, $structure, $filter, $columns, $order=array(), $limit=false, $offset=false) {
		$ls = op5livestatus::instance();

		$fb_visit = new LivestatusFilterBuilderVisitor(array($structure['class'] . 'Pool_Model', "map_name_to_backend"));
		$ls_filter = $filter->visit($fb_visit, false);


		foreach($order as $col_attr) {
			$parts = explode(" ",$col_attr);
			$original_part_0 = $parts[0];
			$parts[0] = call_user_func(array($structure['class'] . 'Pool_Model', 'map_name_to_backend'), $parts[0]);
			/* Throw exception if column is not found */
			if($parts[0] === false) {
				throw new ORMException("Table '". $table ."' has no column '" . $original_part_0 . "'");
			}
			$parts = array_filter($parts);
			$ls_filter .= "Sort: ".implode(" ",$parts)."\n";
		}

		$default_sort = isset($structure['default_sort']) ? $structure['default_sort'] : array();
		foreach($default_sort as $col_attr) {
			$parts = explode(" ",$col_attr);
			$original_part_0 = $parts[0];
			$parts[0] = call_user_func(array($structure['class'] . 'Pool_Model', 'map_name_to_backend'), $parts[0]);
			/* Throw exception if column is not found */
			if($parts[0] === false) {
				throw new ORMException("Table '". $table ."' has no column '" . $original_part_0 . "'");
			}
			$parts = array_filter($parts);
			$ls_filter .= "Sort: ".implode(" ",$parts)."\n";
		}

		if( $offset !== false ) {
			$ls_filter .= "Offset: ".intval($offset)."\n";
		}

		if( $limit !== false ) {
			$ls_filter .= "Limit: ".intval($limit)."\n";
		}

		$valid_columns = false;
		if( $columns !== false ) {
			$processed_columns = array_merge($columns, $structure['key']);
			$processed_columns = call_user_func(array($structure['class'] . 'Set_Model', 'apply_columns_rewrite'), $processed_columns);
			$valid_columns = array();
			foreach($processed_columns as $col) {
				$new_name = call_user_func(array($structure['class'] . 'Pool_Model', 'map_name_to_backend'), $col);
				if($new_name !== false) {
					$valid_columns[] = $new_name;
				}
			}
			$valid_columns = array_unique($valid_columns);
		}

		try {
			list($fetched_columns, $objects, $count) = $ls->query($table, $ls_filter, $valid_columns);
		} catch( op5LivestatusException $e ) {
			throw new ORMException( $e->getPlainMessage(), $table, false, $e );
		}

		if($columns === false) {
			$columns = call_user_func(array($structure['class'] . 'Pool_Model', "get_all_columns_list"));
		}

		return new LivestatusSetIterator($objects, $fetched_columns, $columns, $structure['class'] . '_Model');
	}

	public function stats($table, $structure, $filter, $intersections) {
		$ls = op5livestatus::instance();

		$single = !is_array($intersections);
		if($single) $intersections = array($intersections);

		$fb_visit = new LivestatusFilterBuilderVisitor(array($structure['class'] . 'Pool_Model', "map_name_to_backend"));
		$sb_visit = new LivestatusStatsBuilderVisitor(array($structure['class'] . 'Pool_Model', "map_name_to_backend"));
		$ls_filter = $filter->visit($fb_visit, false);

		$ls_intersections = array();
		foreach( $intersections as $name => $intersection ) {
			if($intersection->table == $table) {
				$ls_intersections[$name] = $intersection->filter->visit($sb_visit, false);
			} // TODO: Error handling...
		}

		try {
			$result = $ls->stats_single($table, $ls_filter, $ls_intersections);
		}
		catch (op5LivestatusException $ex) {
			return false;
		}

		if($single) $result = $result[0];
		return $result;

	}

	public function update($table, $structure, $filter, $values) {
		throw new Exception($table . " is not writable");
	}

	public function delete($table, $structure, $filter) {
		throw new Exception($table . " is not writable");
	}

	public function insert_single($table, $structure, $values) {
		throw new Exception($table . " is not writable");
	}
}
