<?php

/**
 * Represents a set of widgets. The TAC can display one dashboard at a time.
 * The user chooses which dashboard to display.
 */
class Dashboard_Model extends BaseDashboard_Model
{
	/**
	 * Return the dashboard as an array, with widgets included
	 *
	 * @return array('dashboard' => ..., 'widgets' => ...);
	 */
	public function export_array()
	{
		$board = $this->export();
		/* we delete local-only variables */
		unset($board['id']);
		unset($board['username']);

		$ret = array('dashboard' => $board);

		$widget_set = $this->get_dashboard_widgets_set();

		$ret['widgets'] = array();
		foreach ($widget_set as $widget) {
			$w_ary = $widget->export();
			/* delete local-only variables */
			if (isset($w_ary['id'])) {
				unset($w_ary['id']);
			}
			if (isset($w_ary['dashboard'])) {
				unset($w_ary['dashboard']);
			}
			if (isset($w_ary['dashboard_id'])) {
				unset($w_ary['dashboard_id']);
			}
			$ret['widgets'][] = $w_ary;
		}

		return $ret;
	}

	/**
	 * Import an array to replace the current dashboard
	 *
	 * @return Nothing.
	 */
	public function import_array($ary)
	{
		$board_ary = $ary['dashboard'];
		$widgets_ary = $ary['widgets'];

		unset($board_ary['id']);
		foreach ($board_ary as $k => $v) {
			$mname = 'set_' . $k;
			if (!method_exists($this, $mname)) {
				continue;
			}
			$this->$mname($v);
		}
		$this->save();

		/*
		 * Delete widgets after save is successful.
		 * This should really only trigger for the "factory reset"
		 * case when someone replaces their existing dashboard,
		 * so performance isn't very important.
		 */
		foreach ($this->get_dashboard_widgets_set() as $wdg) {
			$wdg->delete();
		}

		foreach ($widgets_ary as $widget_ary) {
			unset($widget_ary['id']);
			$widget_ary['dashboard_id'] = $this->get_id();
			$widget = new Dashboard_Widget_Model();
			foreach ($widget_ary as $k => $v) {
				$mname = 'set_' . $k;
				if (!method_exists($widget, $mname)) {
					continue;
				}
				$widget->$mname($v);
			}
			$widget->save();
		}
	}

	/**
	 * Sets layout and converts to a new layout.
	 * @param $layout string The new layout.
	 */
	public function set_layout($layout) {
		if ($this->get_layout() === $layout) return;

		parent::set_layout($layout);

		// Only changes back and forth between layout 1,3,2 and 3,2,1.
		$widgets = $this->get_dashboard_widgets_set();
		foreach ($widgets as $w) {
			// $pos['c'] is the dashboard cell.
			// $pos['p'] is the position within the cell.
			$pos = $w->get_position();

			if ($layout === '1,3,2') { // 3,2,1 => 1,3,2
				$w->set_position(array(
					'c' => ($pos['c'] + 1) % 6,
					'p' => $pos['p']
				));
			} else { // 1,3,2 => 3,2,1
				$w->set_position(array(
					'c' => ($pos['c'] + 5) % 6,
					'p' => $pos['p']
				));
			}
			$w->save();
		}
	}

	/**
	 * @param $table string, such as 'users' or 'usergroups'
	 * @param $key string, such as 'a user name'
	 */
	public function add_read_perm($table, $key) {
		$quark = PermissionQuarkPool_Model::build($table, $key);
		$quark_ids = array_filter(explode(',', parent::get_read_perm()));
		$quark_ids[] = $quark;
		$quark_ids = array_values(array_unique($quark_ids));
		parent::set_read_perm(','.implode(',', $quark_ids).',');
	}

	/**
	 * @return array ['table1' => ['key1', 'key2', ...], 'table2' => ['key1', 'key2', ...]]
	 */
	public function get_read_perm() {
		// turns ",3,4,5" into array(0 => 3, 1 => 4, 2 => 5)
		$read_perms_exploded = array_filter(explode(',', parent::get_read_perm()));
		$shared_with = array();
		foreach($read_perms_exploded as $permission_quark_id) {
			$pq = PermissionQuarkPool_Model::fetch_by_key($permission_quark_id);
			if(!isset($shared_with[$pq->get_foreign_table()])) {
				$shared_with[$pq->get_foreign_table()] = array();
			}
			$shared_with[$pq->get_foreign_table()][] = $pq->get_foreign_key();
		}
		return $shared_with;
	}

	/**
	 * Overrides previous read permissions
	 *
	 * @param $permissions array => ['table1' => ['key1', 'key2', ...], 'table2' => ['key1', 'key2', ...]]
	 */
	public function set_read_perm(array $permissions = array()) {
		$quarks = array();
		foreach($permissions as $table => $keys) {
			foreach($keys as $key) {
				$quarks[] = PermissionQuarkPool_Model::build($table, $key);
			}
		}
		$quarks = array_values(array_unique($quarks));
		parent::set_read_perm(','.implode(',', $quarks).',');
	}

	/**
	 * Return if the current authenticated user can write to this dashboard
	 *
	 * For now, we are only allowed to edit our own dashboards
	 *
	 * @ninja orm depend[] username
	 *
	 * @return boolean
	 */
	public function get_can_write() {
		$user = Auth::instance()->get_user();
		return $this->get_username() == $user->get_username();
	}

	/**
	 * Discontinue sharing your dashboard with an object
	 *
	 * @param $table string, such as 'users' or 'usergroups'
	 * @param $key string, such as 'a user name'
	 */
	public function remove_read_perm($table, $key) {
		$quark = PermissionQuarkPool_Model::all()
			->reduce_by('foreign_table', $table, '=')
			->reduce_by('foreign_key', $key, '=')
			->one();
		if(!$quark) {
			return;
		}
		// micro optimizations for the win
		$raw_perm_string = str_replace(','.$quark->get_id().',', ',', parent::get_read_perm());
		parent::set_read_perm($raw_perm_string);
	}
}
