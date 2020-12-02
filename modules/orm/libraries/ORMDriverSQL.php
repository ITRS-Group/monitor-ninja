<?php
/**
 * An ORM driver which is backed by a generic (MySQL-like) SQL database
 */
class ORMDriverSQL implements ORMDriverInterface {

	/**
	 * Which visitor should sql_where() use?
	 */
	protected $sql_builder_visitor_class_name = 'LivestatusSQLBuilderVisitor';

	/**
	 * Generate "JOIN" statement based on filter and structure
	 *
	 * @return string
	 */
	private function sql_join($filter, $structure) {
		$sql = "";
		$table = $structure['table'];
		$class = $structure['class'];
		$pool_class = $class . 'Pool_Model';
		$full_structure = $pool_class::get_full_structure();
		foreach ($structure['relations'] as $relation) {
			list($foreign_key, $foreign_table, $key) = $relation;
			$foreign_structure = $full_structure[$foreign_table];
			$ftable = $foreign_structure['table'];
			$join_expr = ' LEFT JOIN '.$ftable.' AS '.$key;
			$join_expr .= ' ON '.implode(' AND ',array_map(function($fk,$lk) use($key,$table) {
				return "$key.$fk = $table.$lk";
			}, $foreign_structure['key'], $foreign_key));
			$sql .= $join_expr;
		}
		return $sql;
	}

	/**
	 * Generate "WHERE" statement based on filter and structure
	 *
	 * @return string
	 */
	private function sql_where($filter, $structure) {
		$pool_class = $structure['class'] . 'Pool_Model';
		$visitor = new $this->sql_builder_visitor_class_name(array($pool_class, "map_name_to_backend"));
		$sql = " WHERE ".$filter->visit($visitor, false);
		return $sql;
	}

	private function get_db_instance($structure) {
		if (isset($structure['db_instance'])) {
			return Database::instance($structure['db_instance']);
		}
		else {
			return Database::instance(false);
		}
	}
	public function count($table, $structure, $filter) {
		$db = $this->get_db_instance($structure);
		$sql = "SELECT COUNT(*) AS count";
		$sql .= " FROM " . $structure['table'];
		$sql .= $this->sql_join($filter, $structure);
		$sql .= $this->sql_where($filter, $structure);
		$q = $db->query($sql);
		$q->result(false);
		$row = $q->current();
		return $row["count"];

	}

	public function it($table, $structure, $filter, $columns, $order=array(), $limit=false, $offset=false) {
		$db = $this->get_db_instance($structure);

		$valid_columns = false;
		$set_class = $structure['class'] . 'Set_Model';
		$pool_class = $structure['class'] . 'Pool_Model';
		if( $columns !== false ) {
			$processed_columns = array_merge($columns, $structure['key']);
			$processed_columns = $set_class::apply_columns_rewrite($processed_columns);
			$valid_columns = array();
			foreach($processed_columns as $col) {
				$new_name = call_user_func(array($pool_class, "map_name_to_backend"), $col);
				if($new_name !== false) {
					$valid_columns[] = $new_name;
				}
			}
			$valid_columns = array_unique($valid_columns);
		}

		$sql = "SELECT ";
		if ($valid_columns === false) {
			$table_names = array($structure['table']);
			foreach( $structure['relations'] as $rel ) {
				$table_names[] = $rel[2];
			}
			$sql .= implode(', ',
				array_map(function($rel) {
					return $rel . '.*';
				},
				$table_names));
		} else {
			$sql .= implode(", ", $valid_columns);
		}
		$sql .= " FROM " . $structure['table'];
		$sql .= $this->sql_join($filter, $structure);
		$sql .= $this->sql_where($filter, $structure);

		$sort = array();
		foreach ($order as $col_attr) {
			$parts = explode(" ",$col_attr,2);
			if(isset($parts[1]) && !preg_match("/^(asc|desc)$/i",$parts[1])) continue;
			$original_part_0 = $parts[0];
			$parts[0] = call_user_func(array($pool_class, "map_name_to_backend"), $parts[0]);
			if($parts[0] === false) {
				throw new ORMException("Table '". $table ."' has no column '" . $original_part_0 . "'");
			}
			$sort[] = implode(" ",$parts);
		}

		$default_sort = isset($structure['default_sort']) ? $structure['default_sort'] : array();
		foreach($default_sort as $col_attr) {
			$parts = explode(" ",$col_attr,2);
			if(isset($parts[1]) && !preg_match("/^(asc|desc)$/i",$parts[1])) continue;
			$original_part_0 = $parts[0];
			$parts[0] = call_user_func(array($pool_class, "map_name_to_backend"), $parts[0]);
			if($parts[0] === false) {
				throw new ORMException("Table '". $table ."' has no column '" . $original_part_0 . "'");
			}
			$sort[] = implode(" ",$parts);
		}

		if(!empty($sort)) {
			$sql .= " ORDER BY ".implode(", ",$sort);
		}

		if( $limit !== false ) {
			$sql .= " LIMIT ";
			$sql .= intval($limit);
			if( $offset !== false ) {
				$sql .= " OFFSET " . intval($offset);
			}
		}

		$q = $db->query($sql);
		$q->result(false, MYSQLI_NUM);

		$fetched_columns_raw = $q->list_fields(true);

		$fetched_columns = array();
		foreach($fetched_columns_raw as $col) {
			if(substr($col,0, strlen($structure['table']) + 1) == $structure['table'] . ".") {
				$fetched_columns[] = substr($col, strlen($structure['table']) + 1);
			} else {
				$fetched_columns[] = $col;
			}
		}

		if($columns === false) {
			$columns = call_user_func(array($pool_class, "get_all_columns_list"));
		}

		return new LivestatusSetIterator($q, $fetched_columns, $columns, $structure['class'] . '_Model');
	}

	public function stats($table, $structure, $filter, $intersections) {
		return array();
	}

	public function update($table, $structure, $filter, $values) {
		$db = $this->get_db_instance($structure);
		$sql = 'UPDATE '.$structure['table'];
		$sql .= $this->sql_join($filter, $structure);
		$delim = " SET ";
		$rename = isset($structure['rename']) ? $structure['rename'] : array();
		foreach($values as $k => $v) {
			if (array_key_exists($k, $rename)) {
				$k = $rename[$k];
			}
			/**
			 * To remove the possibility of ambiguity when the table is joined, add
			 * the updated table's name before the key
			 */
			$sql .= sprintf("%s%s.%s=%s", $delim, $structure['table'], $k, $db->escape($v));
			$delim = ", ";
		}

		$sql .= $this->sql_where($filter, $structure);
		$db->query($sql);
	}

	public function delete($table, $structure, $filter) {
		$db = $this->get_db_instance($structure);
		/* Need to specify "DELETE table FROM table ..." when using LEFT JOIN, doesn't hurt otherwise */
		$sql = "DELETE " . $structure['table'] . " FROM " . $structure['table'];
		$sql .= $this->sql_join($filter, $structure);
		$sql .= $this->sql_where($filter, $structure);
		$db->query($sql);
	}

	public function insert_single($table, $structure, $values) {
		$db = $this->get_db_instance($structure);
		$keys = array();
		$esc_values = array();

		$rename = isset($structure['rename']) ? $structure['rename'] : array();
		foreach($values as $k => $v) {
			if (array_key_exists($k, $rename)) {
				$keys[] = $rename[$k];
			} else {
				$keys[] = $k;
			}
			$esc_values[] = $db->escape($v);
		}

		$sql = 'INSERT INTO '.$structure['table'].' (' . implode(",",
			$keys) . ') VALUES (' . implode(",", $esc_values) . ')';

		$result = $db->query($sql);
		return $result->insert_id();
	}
}
