<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana loader class for anydb
 *
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class any_db_Core {
	public function instance()
	{
		$path = self::path();
		if ($path !== false)
		{
			ini_set('include_path',
			ini_get('include_path').PATH_SEPARATOR.dirname(dirname($path)));
			require_once(dirname($path).'/sql_class.php');
			$params = Kohana::config('database.monitor_reports');
			$config = $params['connection'];
			$obj = new sql_class(
				$config['database'],
				$config['user'],
				$config['pass'],
				$config['port'],
				$config['host'],
				$config['type']
			);

			return $obj->db;
		}
		return false;
	}

	/**
	* Fetch xajax absolute path
	*/
	public function path()
	{
		$path = Kohana::find_file('vendor', 'anydb/sql_class');
		return $path;
	}

}