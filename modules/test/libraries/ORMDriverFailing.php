<?php
/**
 * This driver is used to trigger an ORMDriverException
 */
class ORMDriverFailing implements ORMDriverInterface {

	/** count method that only throws an ORMDriverException */
	public function count($table, $structure, $filter) {
		throw new ORMDriverException('This is a failing test driver.');
	}

	/** it method that only throws an ORMDriverException */
	public function it($table, $structure, $filter, $columns, $order = array(),
		               $limit = false, $offset = false
	) {
		throw new ORMDriverException('This is a failing test driver.');
	}

	/** stats method that only throws an ORMDriverException */
	public function stats($table, $structure, $filter, $intersections) {
		throw new ORMDriverException('This is a failing test driver.');
	}

	/** update method that only throws an ORMDriverException */
	public function update($table, $structure, $filter, $values) {
		throw new ORMDriverException('This is a failing test driver.');
	}

	/** delete method that only throws an ORMDriverException */
	public function delete($table, $structure, $filter) {
		throw new ORMDriverException('This is a failing test driver.');
	}

	/** instert_single method that only throws an ORMDriverException */
	public function insert_single($table, $structure, $values) {
		throw new ORMDriverException('This is a failing test driver.');
	}
}
