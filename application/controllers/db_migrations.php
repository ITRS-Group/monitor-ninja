<?php defined('SYSPATH') OR die('No direct access allowed.');

class Db_Migrations_Controller extends Controller {
	public function __construct()
	{
		if (PHP_SAPI !== "cli") {
			echo "You may not access this.";
			die(1);
		}
		parent::__construct();
		$this->auto_render = false;
		$op5_auth = Op5Auth::factory(array('session_key' => false));
		$op5_auth->force_user(new Op5User_AlwaysAuth());
	}

	/**
	 * reports from per-report-type tables to all-in-one
	 */
	public function v13_to_v14()
	{
		$db = Database::instance();

		$res = $db->query('SELECT * FROM avail_config');
		foreach ($res->result(false) as $result) {
			$objs = $db->query('SELECT `name` FROM avail_config_objects WHERE avail_config_objects.avail_id = '.$result['id']);
			$objects = array();
			foreach ($objs as $obj)
				$objects[] = $obj->name;
			$result['objects'] = $objects;
			$opts = new Avail_options($result);
			if (isset($result['alert_types'])) {
				$op5opts = new Op5reports_options($result);
				$op5opts->add_sub($opts);
				$opts = $op5opts;
			}
			if (!$opts->save($msg))
				print 'avail '.$result['report_name'].': '.$msg."\n";
		}

		$res = $db->query('SELECT * FROM sla_config');
		foreach ($res->result(false) as $result) {
			$objs = $db->query('SELECT name FROM sla_config_objects WHERE sla_config_objects.sla_id = '.$result['id']);
			$objects = array();
			foreach ($objs as $obj)
				$objects[] = $obj->name;
			$result['objects'] = $objects;
			$mnts = $db->query('SELECT value FROM sla_periods WHERE sla_periods.sla_id = '.$result['id']);
			$months = array();
			foreach ($mnts as $mnt)
				$months[] = $mnt->value;
			$opts = new Sla_options($result);
			if (isset($result['alert_types'])) {
				$op5opts = new Op5reports_options($result);
				$op5opts->add_sub($opts);
				$opts = $op5opts;
			}
			reset($months);
			foreach ($res['months'] as $key => $_) {
				$opts['months'][$key] = current($months);
				next($months);
			}
			if (!$opts->save($msg))
				print 'sla '.$result['report_name'].': '.$msg."\n";
		}

		$res = $db->query('SELECT * FROM summary_config');
		foreach ($res->result(false) as $result) {
			$setting = @unserialize($result['setting']);
			if (!$setting)
				continue;
			$setting['report_name'] = $result['report_name'];
			$opts = new Summary_options($setting);
			if (!$opts->save($msg))
				print 'summary '.$result['report_name'].': '.$msg."\n";
		}
	}
}
