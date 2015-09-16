<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Search controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
*/
class Search_Controller extends Ninja_Controller {

	public function __construct () {

		parent::__construct();
		$this->manifest = Module_Manifest_Model::get('search_definition');

	}

	protected function build_query ($table, $ls_query, $query) {
		$query = preg_replace("/\"/", '', $query);
		return sprintf("[%s] %s",
			$table, preg_replace("/\{query\}/", $query, $ls_query)
		);
	}

	protected function get_point ($key, $object) {

		$key = preg_split("/\./", $key);
		$point = $object;

		while ($k = array_shift($key)) {
			$point = $point[$k];
		}

		return $point;

	}

	protected function autocomplete_href ($table, $object) {

		$template = $this->manifest[$table]['autocomplete'];

		foreach ($this->manifest[$table]['columns'] as $key) {
			$value = $this->get_point($key, $object);
			$template = preg_replace("/\{" . $key . "\}/", urlencode($value), $template);
		}

		return $template;

	}

	public function result ($query = '', $limit = 10) {

		$this->_verify_access('ninja.search:read.search');
		$query = $this->input->get('query', $query);

		$username = Auth::instance()->get_user()->username;
		$this->template->content = new View('search/result');

		foreach ($this->manifest as $table => $definition) {

			$ls_query = $this->build_query($table, $definition['query'], $query);
			$set = ObjectPool_Model::get_by_query($ls_query);

			if ($this->mayi->run($set->mayi_resource() . ':read.search')) {

				$setting = array(
					'query' => $ls_query,
					'limit' => $limit
				);

				$model = new Ninja_widget_Model(array(
					'page' => Router::$controller,
					'name' => 'listview',
					'widget' => 'listview',
					'username' => $username,
					'friendly_name' => ucfirst($table),
					'setting' => $setting
				));

				$widget = widget::get($model, $this);
				widget::set_resources($widget, $this);

				$widget->set_fixed();
				$widget->extra_data_attributes['text-if-empty'] = _("No $table found, searching for ".htmlspecialchars($query));
				$this->template->content->widgets[] = $widget->render();
			}

		}

	}

	public function autocomplete ($query = '') {

		$this->_verify_access('ninja.search:read.search');
		$query = $this->input->get('query', $query);

		$results = array();

		foreach ($this->manifest['queries'] as $table => $ls_query) {

			$set = ObjectPool_Model::get_by_query(
				$this->build_query($table, $ls_query, $query)
			);

			if ($this->mayi->run($set->mayi_resource() . ':read.search')) {
				$results[$table] = array();
				foreach ($set->it($definition['columns'], array(), 10) as $o) {
					$object = $o->export();
					$object['link'] = $this->autocomplete_href($table, $object);
					$results[$table][] = $object;
				}
			}

		}

		json::ok($results);

	}

	/**
	 * Translated helptexts for this controller
	 */
	public static function _helptexts($id) {

		$helptexts = array(
			'search_help' => _("Here you may perform a global search for Hosts, Services, Hostgroups and Services, they are searched for by their key column (such as name for Hosts and description for Services). Services are also visible when searching for the Host they reside on."),
			'saved_search_help' => _('Click to save this search for later use. Your saved searches will be available by clicking on the icon just below the search field at the top of the page.'),
			'filterbox' => _('When you start to type, the visible content gets filtered immediately.<br /><br />If you press <kbd>enter</kbd> or the button "Search through all result pages", you filter all result pages but <strong>only through its primary column</strong> (<em>host name</em> for host objects, etc).')
		);

		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		} else {
			echo sprintf(_("This helptext ('%s') is not translated yet"), $id);
		}

	}

}