<?php
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
	 * @return void
	 */
	public function __construct($storage = array(), $mockfile = null, $mockdriver = null) {
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

		if (isset($this->storage[$table]) && count($this->storage[$table]) > 0) {
			foreach ($this->storage[$table] as $row) {
				foreach ($structure["structure"] as $field => $type) {
					if (is_array($type)) {
						list($class_prefix, $field_prefix) = $type;
						$pool_model = $class_prefix . 'Pool_Model';
						$foreign_table = $pool_model::get_table();
						$foreign_key = $pool_model::key_columns();
						foreach ($this->storage[$foreign_table] as $foreign_row) {
							//FIXME: Handle multiple foreign keys in pool model
							if ($foreign_row[$foreign_key[0]] == $row[$field]) {
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
		return new ArrayIterator($data);
	}

	public function stats($table, $structure, $filter, $intersections)
	{
		return array_combine(array_keys($intersections), array_fill(0, count($intersections), 0));
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
				$obj = $model_type::factory_from_setiterator($row, '', false);
				if ( $filter->visit($visitor, $obj)) {
					unset($this->storage[$table][$ix]);
				}
			}
		}
		$this->persist($table);
	}

	public function insert_single($table, $structure, $values)
	{
		if (!isset($this->storage[$table])) $this->storage[$table] = array();
		$result = array_push($this->storage[$table], $values) - 1;
		$this->persist($table);
		return $result;
	}

	/**
	 * Writes application inserted mocked data back into the mock file,
	 * so that it is available on subsequent requests.
	 *
	 * @param $table string
	 */
	protected function persist ($table) {
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
