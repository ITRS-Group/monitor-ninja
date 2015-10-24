<?php
interface ORMDriverInterface {
  public function count($table, $structure, $filter);
  public function it($table, $structure, $filter, $columns, $order=array(), $limit=false, $offset=false);
  public function stats($table, $structure, $filter, $intersections);
  public function update($table, $structure, $filter, $values);
  public function delete($table, $structure, $filter);
  public function insert_single($table, $structure, $values);
}
