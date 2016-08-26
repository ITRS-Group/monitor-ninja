<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Menu model for easily accessable hierarchal menus.
 *
 * Used to create a hierarchal menustructure through namespace set'ing.
 * The hierarchy does not need to be set up in order, a lower node
 * can be added and will create dummy nodes through any part of the hierarchy
 * that does not yet exist. These nodes can later be populated with properties
 * or be left as only sub-section nodes.
 *
 *  E.g.
 *   {Menu_Model}->set('My.Namespace.Foobar', '/something/cool') :
 *   {Menu_Model}->set('My', '/something/my') :
 *     My {
 *       href: '/something/cool'
 *       Namespace {
 *         Foobar {
 *           href: '/something/cool'
 *         }
 *       }
 *     }
 *
 * @author Tobias Sjöndin <tobias.sjondin@op5.com>
 * @version 1.0
 */
class Menu_Model {

	private static $insertion_order = 100;
	private $attributes = array("target" => "_self");
	private $branch = array();
	private $separator = false;
	private $order = 100;
	private $label;
	private $href;
	private $icon = "";
	private $id;
	private $style = null;
	private $label_contains_valid_html = false;

	/**
	 * Instantiates a new Menu model node, children within the structure use
	 * the same model as the root node.
	 *
	 * @param $label       What label to display
	 * @param $href        What URL this item should link to
	 * @param $order       At what position should this item be rendered
	 *                             (relative to others at the same level), lower value is higher priority
	 * @param $icon        What icon to use, within ninja this is an icon-class
	 * @param $attributes  Additional attributes to add to the element
	 */
	public function __construct ($label = NULL, $href = NULL, $order = null, $icon = false, array $attributes = array()) {

		if (is_null($order)) {
			$order = self::$insertion_order++;
		}
		$this->label = $label;
		$this->order = $order;
		$this->href = $href;
		$this->icon = $icon;

		$this->attributes = array_merge($this->attributes, $attributes);
		$this->id = $this->standardize($label);


	}

	/**
	 * @return boolean
	 */
	public function has_children () {
		return (count($this->branch) > 0);
	}

	/**
	 * This nodes unique identifier
	 * @return string This nodes unique identifier
	 */
	public function get_id () {
		return $this->id;
	}

	/**
	 * Sets this menu item as a separator
	 *
	 * @param $title Optional title for separator
	 */
	public function set_separator ($title = null) {
		$this->label = $title;
		$this->separator = true;
	}

	/**
	 * Is this menu item a separator
	 *
	 * @return bool
	 */
	public function is_separator () {
		return $this->separator;
	}

	/**
	 * Set the position of this node relative to the other node's order value
	 * within this level.
	 *
	 * @param $order  Relative order to render the item at in this level
	 * @return Menu_Model     Returns $this for chainability
	 */
	public function set_order ($order) {
		if (is_int($order)) { $this->order = $order; }
		return $this;
	}

	/**
	 * Returns this nodes $order value
	 * @return integer  This nodes $order value
	 */
	public function get_order () {
		return $this->order;
	}

	/**
	 * Sets this nodes $icon value
	 *
	 * @param $icon   The icon value to set
	 * @return Menu_Model    Returns $this for chainability
	 */
	public function set_icon ($icon) {
		if (is_string($icon)) { $this->icon = $icon; }
		return $this;
	}

	/**
	 * Returns this nodes $icon value
	 * @return string  This nodes $icon value
	 */
	public function get_icon () {
		return $this->icon;
	}

	/**
	 * Sets this nodes $style value
	 *
	 * Valid styles is "normal" and "image"
	 *
	 * @param $style   The style value to set, null for inherit from parent
	 * @return Menu_Model    Returns $this for chainability
	 */
	public function set_style ($style) {
		$this->style = $style;
		return $this;
	}

	/**
	 * Returns this nodes $style value
	 * @return string  This nodes $style value
	 */
	public function get_style () {
		return $this->style;
	}

	/**
	 * Sets this nodes $href value
	 *
	 * @param $href   The href value to set
	 * @return Menu_Model    Returns $this for chainability
	 */
	public function set_href ($href) {
		if (is_string($href)) { $this->href = $href; }
		return $this;
	}

	/**
	 * Returns this nodes $href value
	 * @return string  This nodes $href value
	 */
	public function get_href () {
		return $this->href;
	}

	/**
	 * Sets this nodes $attributes value
	 *
	 * @param $attributes   The attributes value to set
	 * @return Menu_Model         Returns $this for chainability
	 */
	public function set_attributes ($attributes) {
		if (is_array($attributes)) { $this->attributes = array_merge($this->attributes, $attributes); }
		return $this;
	}

	/**
	 * Sets this nodes $label value as "valid html", meaning that it does not
	 * need further escaping in the view layer. This complements @see set_label()
	 *
	 * @param $html   The label value to set
	 * @return Menu_Model     Returns $this for chainability
	 */
	public function set_html_label ($html) {
		if (is_string($html)) {
			$this->label_contains_valid_html = true;
			$this->label = $html;
		}
		return $this;
	}

	/**
	 * Returns this nodes $attributes value
	 * @return array  This nodes $attributes value
	 */
	public function get_attributes () {
		$this->attributes['data-menu-id'] = $this->get_id();
		if ($this->has_children()) {
			if (isset($this->attributes['class'])) {
				$this->attributes['class'] .= ' menu-section';
			} else {
				$this->attributes['class'] = 'menu-section';
			}
		}
		return $this->attributes;
	}

	/**
	 * Sets this nodes $label value
	 *
	 * @param $label   The label value to set
	 * @return Menu_Model     Returns $this for chainability
	 */
	public function set_label ($label) {
		if (is_string($label)) { $this->label = $label; }
		return $this;
	}

	/**
	 * @return string proper html to use as an element
	 */
	public function get_label_as_html() {
		if($this->label_contains_valid_html) {
			return $this->label;
		}
		return html::specialchars($this->label);
	}

	/**
	 * Returns this nodes child-nodes
	 * @return Menu_Model[]  This nodes child-nodes
	 */
	public function get_branch () {

		usort($this->branch, function ($a, $b) {
			return ($a->get_order() === $b->get_order()) ? 0 : ($a->get_order() < $b->get_order()) ? -1 : 1;
		});

		return $this->branch;

	}

	/**
	 * Standardizes a namespace segment string
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	private function standardize ($name) {
		return strtolower(preg_replace('/[^\w]+/', '_', $name));
	}

	/**
	 * Returns the node reflected by the given namespace.
	 * If no node exists return null.
	 *
	 * @param $namespace     Full namespace to the node you wish to access
	 * @return Menu_Model              Returns the node identified by the namespace
	 * @return null                    If no node is found it returns null
	 */
	public function get ($namespace) {

		$namespace = explode('.', $namespace);
		$last = count($namespace) - 1;
		$point = $this;

		foreach ($namespace as $index => $name) {

			$id = $this->standardize($name);
			foreach ($point->branch as $child) {
				if ($child->id === $id) {
					$point = $child;
					if ($index === $last) {
						return $point;
					}
					break;
				}
			}

		}

		return null;

	}

	/**
	 * Traverses a namespace and instantiates dummy-nodes where required
	 * in order to reach the node that was requested and returns that node.
	 *
	 * @param  $namespace  The namespace to search for
	 * @return Menu_Model      The node the namespace identifies
	 */
	private function build ($namespace) {

		if($namespace === null)
			return $this;

		$namespace = explode('.', $namespace);
		$target = $this;

		for ($index = 0; $index < count($namespace); $index++) {

			$names = array_slice($namespace, 0, $index + 1);
			$point = $target->get(implode('.', $names));

			if (!$point) {
				$point = new Menu_Model($namespace[$index]);
				$target->branch[] = $point;
			}

			$target = $point;

		}

		return $target;

	}

	/**
	 * Sets properties on the node identified by the namespace,
	 * if the node or hierarchy doesn'texist it will be created.
	 *
	 * See constructor for usage of parameters.
	 *
	 * @param $namespace   Full namespace to the node you wish to create or manipulate
	 * @param $href        Link href or null
	 * @param $order       Link order or null
	 * @param $icon        Link icon or null
	 * @param $attributes  Link attributes array or null
	 * @return Menu_Model  Returns $this for chainability
	 */
	public function set ($namespace, $href = NULL, $order = NULL, $icon = false, array $attributes = array()) {

		$target = $this->build($namespace);
		$target->set_icon($icon)
			->set_href($href)
			->set_order($order)
			->set_attributes($attributes);

		return $this;

	}

	/**
	 * Attach a submenu to a menu
	 *
	 * The submenu needs to have its orders, icons and other parametes set correctly
	 *
	 * @param $namespace   Full namespace to the node you wish to create or manipulate
	 * @param $node        The subtree, either as Menu_Model or a View
	 * @return Menu_Model  Returns $this for chainability
	 */
	public function attach($namespace, $node) {
		$target = $this->build($namespace);
		$target->branch[] = $node;
		return $this;
	}
}
