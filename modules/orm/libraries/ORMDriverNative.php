<?php
/**
 * A ORM data source driver which only works against native PHP
 * data structures to support its interface. Useful for mocking!
 */
class ORMDriverNative implements ORMDriverInterface {

	private $storage = array();

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
				$obj = $model_type::factory_from_setiterator($row, '', $columns);
				if ( $filter->visit($visitor, $obj)) {
					$data[] = $obj;
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
					var_dump($obj);
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
