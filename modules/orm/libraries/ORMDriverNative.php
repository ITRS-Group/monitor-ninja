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
	 * Constructor for this class.
	 * @param $storage array An associative array over the table space that this driver serves
	 * @return void
	 */
	public function __construct($storage = array()) {
		$this->storage = $storage;
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
			foreach ( $this->storage[$table] as $row) {
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

				if ( $filter->visit($visitor, $row)) {
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
		throw new ORMException("Unimplemented driver operation update()");
	}

	public function delete($table, $structure, $filter)
	{
		$visitor = new NativeFilterBuilderVisitor();

		$model_type = $structure['class'] . '_Model';
		if (isset($this->storage[$table]) && count($this->storage[$table]) > 0) {
			foreach ( $this->storage[$table] as $ix => $row) {
				$obj = $model_type::factory_from_setiterator($row, '', $columns);
				if ( $filter->visit($visitor, $obj)) {
					unset($this->storage[$table][$ix]);
				}
			}
		}
	}

	public function insert_single($table, $structure, $values)
	{
		if (!isset($this->storage[$table])) $this->storage[$table] = array();
		return array_push($this->storage[$table], $values) - 1;
	}
}
