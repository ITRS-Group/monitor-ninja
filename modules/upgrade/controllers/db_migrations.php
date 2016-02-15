<?php defined('SYSPATH') OR die('No direct access allowed.');
require_once('op5/auth/Auth.php');
require_once(__DIR__.'/../../../modules/reports/libraries/Report_options.php');
require_once(__DIR__.'/../../../modules/reports/libraries/Avail_options.php');
require_once(__DIR__.'/../../../modules/reports/libraries/Sla_options.php');
require_once(__DIR__.'/../../../modules/reports/libraries/Summary_options.php');

/**
 * Handles CLI calls used in installation & upgrade scripts
 */
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
		$op5_auth->force_user(new User_AlwaysAuth_Model());
	}


	private function v13_helper(Report_options $report, $data)
	{
		static $seen_schedules = array();
		$db = Database::instance();
		$db->query('UPDATE saved_reports SET created_by = ' . $db->escape($data['username']) . ', updated_by = ' . $db->escape($data['username']) . ' WHERE id = ' . (int)$report['report_id']);
		$res = $db->query('SELECT id FROM scheduled_reports WHERE report_id = ' . (int)$data['id'] . ' AND report_type_id = (select id from scheduled_report_types where identifier = ' . $db->escape($report::$type) . ')');
		$tmp_seen = array();
		foreach ($res->result(false) as $row)
		{
			$tmp_seen[] = $row['id'];
		}
		$db->query('UPDATE scheduled_reports SET report_id = ' . (int)$report['report_id'] . ' WHERE report_id = ' . (int)$data['id'] . ' AND report_type_id = (select id from scheduled_report_types where identifier = ' . $db->escape($report::$type) . ')' . (empty($seen_schedules) ? '' : ' AND id NOT IN ('.implode(', ', $seen_schedules).')'));
		$seen_schedules = array_merge($seen_schedules, $tmp_seen);
	}

	/**
	 * reports from per-report-type tables to all-in-one
	 */
	public function v13_to_v14()
	{
		$db = Database::instance();

		try {
			$res = $db->query('SELECT * FROM avail_config');
		} catch(Kohana_Database_Exception $e) {
			// This most certainly (hrrm) was an installation
			// instead of an upgrade, and as such, we already have
			// the merlin.saved_reports table.
			//
			// Since it should be the usual case, we're keeping
			// quiet. Shhh.
			return;
		}
		foreach ($res->result(false) as $result) {
			$objs = $db->query('SELECT `name` FROM avail_config_objects WHERE avail_config_objects.avail_id = '.$result['id']);
			$objects = array();
			foreach ($objs->result(false) as $obj)
				$objects[] = $obj['name'];
			$result['objects'] = $objects;
			$opts = new Avail_options($result);
			if (!$opts->save($msg)) {
				print 'avail '.$result['id'].' '.$result['report_name'].': '.$msg."\n";
				continue;
			}
			# reimplement op5reports inside ninja :'(
			if (isset($result['alert_types'])) {
				$availprops = $opts->properties();
				$sopts = new Summary_options($result);
				$sql = "INSERT INTO saved_reports_options(report_id, name, value) VALUES ";
				$sprops = $sopts->properties();
				$rows = array();
				foreach ($result as $key => $val) {
					if ($key != 'use_pnp' && $key != 'use_summary' && !isset($sprops[$key]))
						continue;
					if (isset($availprops[$key]))
						continue;
					$rows[] = '(' . (int)$opts['report_id'] . ', ' . $db->escape($key) . ', ' . $db->escape($val) . ')';
				}
				$sql .= implode(', ', $rows);
				$db->query($sql);
			}
			$this->v13_helper($opts, $result);
		}

		$res = $db->query('SELECT * FROM sla_config');
		foreach ($res->result(false) as $result) {
			$objs = $db->query('SELECT name FROM sla_config_objects WHERE sla_config_objects.sla_id = '.$result['id']);
			$objects = array();
			foreach ($objs->result(false) as $obj)
				$objects[] = $obj['name'];
			$result['objects'] = $objects;
			$mnts = $db->query('SELECT value FROM sla_periods WHERE sla_periods.sla_id = '.$result['id']);
			$months = array();
			foreach ($mnts as $mnt)
				$months[] = $mnt->value;
			$opts = new Sla_options($result);
			reset($months);
			$reindexed_months = array();
			foreach ($opts['months'] as $key => $_) {
				$reindexed_months[$key] = current($months);
				next($months);
			}
			$opts['months'] = $reindexed_months;
			if (!$opts->save($msg)) {
				print 'sla '.$result['id'].' '.$result['report_name'].': '.$msg."\n";
				continue;
			}
			# reimplement op5reports inside ninja :'(
			if (isset($result['alert_types'])) {
				$slaprops = $opts->properties();
				$sopts = new Summary_options($result);
				$sql = "INSERT INTO saved_reports_options(report_id, name, value) VALUES ";
				$sprops = $sopts->properties();
				$rows = array();
				foreach ($sopts as $key => $val) {
					if ($key != 'use_pnp' && $key != 'use_summary' && !isset($sprops[$key]))
						continue;
					if (isset($slaprops[$key]))
						continue;
					$rows[] = '(' . (int)$opts['report_id'] . ', ' . $db->escape($key) . ', ' . $db->escape($val) . ')';
				}
				$sql .= implode(', ', $rows);
				$db->query($sql);
			}
			$this->v13_helper($opts, $result);
		}

		$res = $db->query('SELECT * FROM summary_config');
		foreach ($res->result(false) as $result) {
			$setting = @unserialize($result['setting']);
			if (!$setting)
				continue;
			unset($setting['report_id']);
			$setting['report_name'] = $result['report_name'];
			$opts = new Summary_options($setting);
			if (!$opts->save($msg)) {
				print 'summary '.$result['id'].' '.$result['report_name'].': '.$msg."\n";
				continue;
			}
			$this->v13_helper($opts, $result);
		}
	}
}
