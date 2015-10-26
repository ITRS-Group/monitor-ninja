<?php
/**
 * Defines required operations for a Ninja ORM data source (for
 * example, a RDBMS or a particular file format).
 */
interface ORMDriverInterface {
  /**
   * Get the number of objects in a table that matches a given
   * filter
   * @param $table string The table to perform the count on
   * @param $structure array An associative array describing this table
   * @param $filter LivestatusFilterBase The filter
   * @return int The number of objects in $table matching $filter
   */
  public function count($table, $structure, $filter);

  /**
   * Get the objects in a table that matches a given
   * filter
   * @param $table string The table to perform the query on
   * @param $structure array An associative array describing this table
   * @param $filter LivestatusFilterBase The filter
   * @param $columns array An array of strings denoting the columns to expose in the iterator
   * @param $order array An array of strings denoting the columns to order by in the format "<column [asc|desc]>"
   * @param $limit int Upper bound on the number of objects in the returned iterator
   * @param $offset int Specifies the offset
   * @return Iterator Iterator over the objects in $table matching $filter
   */
  public function it($table, $structure, $filter, $columns, $order=array(), $limit=false, $offset=false);

  /**
   * Returns an arbitrary (driver-specific) status summary of this table
   * @param $table string The table
   * @param $structure array An associative array describing this table
   * @param $filter LivestatusFilterBase The filter
   * @param $intersections mixed
   * @todo document $intersections if you know what it does
   */
  public function stats($table, $structure, $filter, $intersections);

  /**
   * Update one or more objects in a table matching a filter with a given set
   * of values
   * @param $table string The table to update
   * @param $structure array An associative array describing this table
   * @param $filter LivestatusFilterBase The filter
   * @param $values array A mapping from fields to values
   * @return void
   */
  public function update($table, $structure, $filter, $values);

  /**
   * Delete objects matching a given filter from a table
   * @param $table string The table to delete from
   * @param $structure array An associative array describing this table
   * @param $filter LivestatusFilterBase The filter
   * @return void
   */
  public function delete($table, $structure, $filter);

  /**
   * Insert an object into a specified table
   * @param $table string The table to insert the object into
   * @param $structure array An associative array describing this table
   * @param $values array A mapping from fields to values
   * @return mixed An identifier for the inserted object, which should be unique to
   * this driver/table combination
   */
  public function insert_single($table, $structure, $values);
}
