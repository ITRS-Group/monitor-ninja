<?php

/**
 * Models the interface of saved queries for the listview search filters
 */
class LSFilter_Saved_Queries_Model extends Model {
	const tablename = 'ninja_saved_filters'; /**< Name of saved searches table */

	/**
	 * An associative array, indexed on table name, values as another associative array,
	 * which maps a search name to a query, for the class of static saved searches.
	 */
	private static $saved_queries = array(
/*			'hosts' => array(
					'hosts up'                   => '[hosts] state=0 and has_been_checked=1',
					'hosts down'                 => '[hosts] state=1 and has_been_checked=1',
					'hosts unreachable'          => '[hosts] state=2 and has_been_checked=1',
					'hosts pending'              => '[hosts] has_been_checked=0',
					'hosts all'                  => '[hosts] all',
					'problem hosts'              => '[hosts] has_been_checked != 0 and state != 0',
					'unhandled host problems'    => '[hosts] has_been_checked != 0 and state != 0 and scheduled_downtime_depth = 0 and acknowledged = 0'
			),
			'services' => array(
					'services ok'                => '[services] state=0 and has_been_checked=1',
					'services warning'           => '[services] state=1 and has_been_checked=1',
					'services critical'          => '[services] state=2 and has_been_checked=1',
					'services unknown'           => '[services] state=3 and has_been_checked=1',
					'services pending'           => '[services] has_been_checked=0',
					'services all'               => '[services] all',
					'problem services'           => '[services] has_been_checked != 0 and state != 0',
					'unhandled service problems' => '[services] has_been_checked != 0 and state != 0 and scheduled_downtime_depth = 0 and acknowledged = 0'
			)*/
	);

	/**
	 * Get the set of static search queries, possible given a table name
	 */
	private static function get_static_queries( $table = false ) {
		/* TODO: return table names */
		if($table==false) {
			$queries = array_reduce( self::$saved_queries, 'array_merge', array());
		} else if(isset(self::$saved_queries[$table])) {
			$queries = self::$saved_queries[$table];
		} else {
			$queries = array();
		}
		return array_map(function($k,$v){
			return array('name'=>$k,'query'=>$v,'scope'=>'static');
		},array_keys($queries),array_values($queries));
	}

	/**
	 * Get a set of database queries, possible given a table name
	 */
	private static function get_db_queries( $table = false ) {
		$db = Database::instance();
		$user = Auth::instance()->get_user()->username;

		$table_filter = "";
		if($table !== false) {
			$table_filter = " AND filter_table = ".$db->escpae($table);
		}

		$sql = "SELECT * FROM ".self::tablename." WHERE (username=".$db->escape($user)." OR username IS NULL)$table_filter";
		$res = $db->query($sql);

		$queries = array();
		foreach($res as $row) {
			$queries[] = array( 'name' => $row->filter_name, 'table' => $row->filter_table, 'query' => $row->filter, 'scope' => ($row->username?'user':'global') );
		}

		return $queries;
	}

	/**
	 * Get a set of queries, possible given a table name
	 */
	public static function get_queries( $table=false ) {
		$queries = array_merge(
				self::get_static_queries($table),
				self::get_db_queries($table)
				);

		return $queries;
	}

	/**
	 * get a given query by name, possible by table name
	 */
	public static function get_query( $name, $table=false ) {
		foreach( self::get_queries() as $query )
			if( $query['name'] == $name )
				return $query['query'];
		return false;
	}
	/**
	 * Save a query to the database
	 *
	 * @param $name string
	 * @param $query string
	 * @param $scope string "user" or "global"
	 * @throws Exception if query can't be saved
	 */
	public static function save_query( $name, $query, $scope ) {
		$db = Database::instance();
		$parser = new LSFilter(new LSFilterPP(), new LSFilterMetadataVisitor());
		$metadata = $parser->parse( $query );

		if( $metadata === false ) throw new Exception("Error when type checking");

		$user = Auth::instance()->get_user()->username;
		if( $scope == 'global' ) {
			if( !op5auth::instance()->authorized_for('saved_filters_global') ) {
				return "Not authorized to create global queries";
			}
			$user = null;
		}

		switch( $scope ) {
			case 'user':
			case 'global':
				/* Those are ok, break out of switch */
				break;
			case 'static':
				throw new Exception("Can not save to statis scope");
			default:
				throw new Exception("Unknown scope");
		}

		/* It should be an update on duplicate key, but that doesn't work well with null-valued columns, delete instead */
		if( $user === null ) {
			$sql_query = "DELETE FROM ".self::tablename." WHERE username IS NULL AND filter_name = %s";
			$args = array($name);
		} else {
			$sql_query = "DELETE FROM ".self::tablename." WHERE username = %s AND filter_name = %s";
			$args = array($user, $name);
		}
		$sql_query = vsprintf( $sql_query, array_map( array($db, 'escape'), $args ) );
		$res = $db->query($sql_query);

		/* And insert it's value */
		$sql_query = "INSERT INTO ".self::tablename." (username, filter_name, filter_table, filter, filter_description) VALUES (%s,%s,%s,%s,%s)";
		$args = array($user, $name, $metadata['name'], $query, $name);
		$sql_query = vsprintf( $sql_query, array_map( array($db, 'escape'), $args ) );
		$db->query($sql_query);
	}
	/**
	 * Delete a query to the database
	 */
	public static function delete_query( $id ) {
		$db = Database::instance();

		$user = Auth::instance()->get_user()->username;

		$sql_query = "DELETE FROM ".self::tablename." WHERE username = %s AND id = %s";
		$args = array($user, $id);

		if( op5auth::instance()->authorized_for('saved_filters_global') ) { /* FIXME: Delete from global scope */
			$sql_query = "DELETE FROM ".self::tablename." WHERE (username = %s OR username IS NULL) AND id = %s";
		}

		$sql_query = vsprintf( $sql_query, array_map( array($db, 'escape'), $args ) );
		$res = $db->query($sql_query);
		return false;
	}
}
