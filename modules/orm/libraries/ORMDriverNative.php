<?php

/**
 * Native driver specific exception
 */
class ORMDriverNativeException extends Exception {}

/**
 * A ORM data source driver which only works against native PHP
 * data structures to support its interface. Useful for mocking!
 */
class ORMDriverNative implements ORMDriverInterface {

	/**
	 * Temporary data storage
	 */
	protected $storage = array();

	/**
	 * The file the data is mocked into
	 */
	protected $mockfile = null;

	/**
	 * The driver being mocked
	 */
	protected $mockdriver = null;

	/**
	 * Constructor for this class.
	 *
	 * @param $storage array An associative array over the table space that this driver serves
	 * @param $mockfile string Path to the mock datafile
	 * @param $mockdriver string Named of the mocked driver
	 * @throws ORMDriverNativeException when $storage is not good set of tables
	 * @return void
	 */
	public function __construct($storage = array(), $mockfile = null, $mockdriver = null) {
		$existing_tables = Module_Manifest_Model::get('orm_structure');
		$wrongly_defined_tables = array_keys(array_diff_key($storage, $existing_tables));
		if($wrongly_defined_tables) {
			$bad_tables = implode("', '", $wrongly_defined_tables);
			throw new ORMDriverNativeException("Trying to mock ".
				"front end table(s) '".$bad_tables."' which ".
				"does not exist. Check the orm_structure.php ".
				"files for your module.");
		}
		$this->storage = $storage;
		$this->mockfile = $mockfile;
		$this->mockdriver = $mockdriver;
	}

	public function count($table, $structure, $filter) {
		return count($this->it($table, $structure, $filter, false));
	}

	public function it($table, $structure, $filter, $columns, $order=array(), $limit=false, $offset=false)
	{

		$visitor = new NativeFilterBuilderVisitor();
		if ($columns === false)
			$columns = call_user_func(array($structure['class'] . 'Pool_Model', "get_all_columns_list"));
		$model_type = $structure['class'] . '_Model';
		$data = array();

		if (intval($offset) !== 0 /*If it's zero, it doesn't matter */) {
			throw new ORMDriverNativeException("Non-zero 'offset' specified (" . $offset . "), but not implemented");
		}

		$pools = array();
		if (isset($this->storage[$table]) && count($this->storage[$table]) > 0) {
			foreach ($this->storage[$table] as $row) {
				if ($limit !== false && count($data) === intval($limit))
					break;

				foreach ($structure["structure"] as $field => $type) {
					if (is_array($type)) {
						list($class_prefix, $field_prefix) = $type;
						$pool_model = $class_prefix . 'Pool_Model';
						if(!isset($pools[$pool_model])) {
							$pools[$pool_model] = new $pool_model();
						}
						$foreign_key = call_user_func(array($pools[$pool_model], "key_columns"));
						if(isset($structure["relations"])) {
							/* SQL explicit references */
							foreach($structure["relations"] as $rel) {
								list($ref_key, $ref_table, $ref_field) = $rel;
								if($ref_field == $field) {
									$foreign_table = $ref_table;
									$my_foreign_ref = $ref_key;
									break;
								}
							}
						} else {
							/* Livestatus implicit references */
							$foreign_table = $pool_model::get_table();
							$my_foreign_ref = array($field);
						}
						foreach ($this->storage[$foreign_table] as $foreign_row) {
							//FIXME: Handle multiple foreign keys in pool model
							if ($foreign_row[$foreign_key[0]] == $row[$my_foreign_ref[0]]) {
								$row[$field] = $foreign_row;
								break;
							}
						}
					}
				}

				/* Need to create the model to set all model
				 * fields to reduce boilerplate in testcases when
				 * mocking. This avoids fatal Undefined index errors */
				if ($filter->visit($visitor, $row)) {
					$data[] = $model_type::factory_from_array($row, $columns);
				}
			}
		}
		$ai = new ArrayIterator($data);
		foreach ($order as $ord) {
			$ai->uasort(function ($a, $b) use($ord) {
				if ($a->{'get_' . $ord}() < $b->{'get_' . $ord}()) {
					return -1;
				}
				if ($a->{'get_' . $ord}() > $b->{'get_' . $ord}()) {
					return 1;
				}
				return 0;
			});
		}
		return $ai;
	}

	public function stats($table, $structure, $filter, $intersections)
	{

		if(!array_key_exists($table, $this->storage)) {
			return array_combine(array_keys($intersections), array_fill(0, count($intersections), 0));
		}

		$visitor = new NativeFilterBuilderVisitor();
		$stats = array();

		foreach ($intersections as $name => $intersection) {
			if ($intersection->table == $table) {
				$stats[$name] = 0;
				foreach ($intersection as $object) {
					if ($filter->visit($visitor, $object->export())) {
						$stats[$name] += 1;
					}
				}
			}
		}

		return $stats;

	}

	public function update($table, $structure, $filter, $values)
	{
		$touched = false;
		if (isset($this->storage[$table]) && count($this->storage[$table]) > 0) {
			$visitor = new NativeFilterBuilderVisitor();
			foreach ( $this->storage[$table] as $ix => $row) {
				if ( $filter->visit($visitor, $row)) {
					foreach($values as $old_key => $new_value) {
						$this->storage[$table][$ix][$old_key] = $new_value;
					}
					$touched = true;
				}
			}
		}
		if($touched) {
			$this->persist($table);
		}
	}


	public function delete($table, $structure, $filter)
	{
		if (isset($this->storage[$table]) && count($this->storage[$table]) > 0) {
			$visitor = new NativeFilterBuilderVisitor();
			foreach ( $this->storage[$table] as $ix => $row) {
				if ( $filter->visit($visitor, $row)) {
					unset($this->storage[$table][$ix]);
				}
			}
		}
		$this->persist($table);
	}

	public function insert_single($table, $structure, $values)
	{
		if (!isset($this->storage[$table])) $this->storage[$table] = array();

		/*
		 * This is mainly used for mocking in tests. All tables either have
		 * autoincrement id, or no id. Thus, update id is a safe bet
		 *
		 * The id should not be 0 during mocking as the default value of ints
		 * in the ORM is 0 this will be seen as unchanged and therefor not
		 * updated/saved to persist in the object baseclasses, leaving us with
		 * empty indexes in for example object relations.
		 */
		$id = empty($this->storage[$table]) ? 1 : (max(array_keys($this->storage[$table]))+1);
		$values['id'] = $id; /* tables is ordered from id=1, arrays from 0 */

		$this->storage[$table][$id] = $values;
		$this->persist($table);

		return $id;
	}

	/**
	 * Writes application inserted mocked data back into the mock file,
	 * so that it is available on subsequent requests.
	 *
	 * @param $table string
	 */
	protected function persist ($table) {
		/* If there is no mockfile, we shouldn't persist. Probably unit tests then */
		if($this->mockfile === null)
			return;

		$json_str = file_get_contents($this->mockfile);
		if (!$json_str) {
			$log->log("error", "Could not read mock data from '$this->mockfile'");
			return;
		}
		$json_conf = json_decode($json_str, true);
		if ($json_conf === null) {

			$log->log("error", "Could not decode mock data from '$this->mockfile'");
			return;
		}

		$json_conf[$this->mockdriver][$table] = $this->storage[$table];
		$json_str = json_encode($json_conf);

		if ($json_str === false) {
			$log->log("error", "Could not encode mock data for '$this->mockdriver $table'");
			return;
		}

		if (!file_put_contents($this->mockfile, $json_str)) {
			$log->log("error", "Could not persist mock data for '$this->mockdriver $table' into '$this->mockfile'");
			return;
		}

	}
}
