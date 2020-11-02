<?php

/**
 * A quicklink
 */
class Quicklink_Model {

	/**
	 * The href of a quicklink
	 */
	private $href = "";

	/**
	 * The icon of a quicklink
	 */
	private $icon = "";

	/**
	 * The attributes of a quicklink
	 */
	private $attributes = array(
		'class' => 'image-link'
	);

	/**
	 * Constructs the quicklink
	 *
	 * @param $icon string Icon name
	 * @param $href string The href to link to
	 * @param $attributes array Extra HTML attributes for quicklink
	 */
	function __construct ($icon, $href, array $attributes = array()) {

		$this->icon = $icon;
		$this->href = $href;
		$this->attributes = array_merge($this->attributes, $attributes);

	}

	/**
	 * Gets the HTML render of a quicklink
	 *
	 * @return string
	 */
	function get_html () {

		$attributes = array();
		foreach ($this->attributes as $key => $value) {
			$attributes[] = html::specialchars($key) . '="' . html::specialchars($value) . '"';
		}

		return '<li><a href="' . $this->href . '" ' .implode(' ', $attributes) . '>' .
			'<span class="icon-16 x16-'.$this->icon.'"></span><span class="quicklink-badge"></span>' .
		'</li>';

	}

}
