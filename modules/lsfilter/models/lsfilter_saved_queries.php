<?php

class LSFilter_Saved_Queries_Model extends Model {
	const tablename = 'ninja_saved_queries'; /**< Name of saved searches table */

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

	private static function get_static_queries( $table = false ) {
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
	
	private static function get_db_queries( $table = false ) {
		$db = Database::instance();
		$user = Auth::instance()->get_user()->username;
		
		$table_filter = "";
		if($table !== false) {
			$table_filter = " AND query_table = ".$db->escpae($table);
		}
		
		$sql = "SELECT * FROM ".self::tablename." WHERE (username=".$db->escape($user)." OR username='-')$table_filter";
		$res = $db->query($sql);
		
		$queries = array();
		foreach($res as $row) {
			$queries[] = array( 'name' => $row->query_name, 'query' => $row->query, 'scope' => ($row->username?'user':'global') );
		}

		return $queries;
	}
	
	public static function get_queries( $table=false ) {
		$queries = array_merge(
				self::get_static_queries($table),
				self::get_db_queries($table)
				);
		
		return $queries;
	}

	public static function get_query( $name, $table=false ) {
		foreach( self::get_queries() as $query )
			if( $query['name'] == $name )
				return $query['query'];
		return false;
	}
	
	
	public static function save_query( $name, $query, $scope ) {
		$db = Database::instance();
		$parser = new LSFilter_Core(new LSFilterPP_Core(), new LSFilterMetadataVisitor_Core());
		$metadata = $parser->parse( $query );
		
		if( $metadata === false ) return "Error when type checking";
		
		$user = Auth::instance()->get_user()->username;
		if( $scope == 'global' ) $user = '-'; /* FIXME: no special values! Do a select, then update/insert - make oracle-compatible */
		
		switch( $scope ) {
			case 'user':
			case 'global':
				$sql_query = "INSERT INTO ".self::tablename." (username, query_name, query_table, query, query_description) VALUES (%s,%s,%s,%s,%s) ON DUPLICATE KEY UPDATE query=%s";
				$args = array($user, $name, $metadata['name'], $query, $name, $query);
				break;
			case 'static':
				return "Can not save to statis scope";
			default:
				return "Unknown scope";
		}
		
		$sql_query = vsprintf( $sql_query, array_map( array($db, 'escape'), $args ) );
		$res = $db->query($sql_query);
		return false;
	}
}
