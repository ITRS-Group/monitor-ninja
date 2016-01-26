<?php

/**
 * Ninja Toolbar Management
 *
 *
 */
class Toolbar_Controller extends Ninja_Controller {

	public $title = false;
	public $subtitle = false;
	public function __construct ( $title = false, $subtitle = false ) {

		$this->title = ( gettype( $title ) == "string" ) ? $title: false;
		$this->subtitle = ( gettype( $subtitle ) == "string" ) ? $subtitle: false;

	}

	private $should_render_buttons = false;
	public function should_render_buttons($should_render_buttons = true) {
		$this->should_render_buttons = $should_render_buttons;
	}

	private $buttons = array();
	public function button ( $title, $attr = false ) {
		$this->should_render_buttons(true);
		if ( !$attr ) $attr = array();

		$this->buttons[ ] = array(
			"name" => $title,
			"attr" => $attr
		);

	}

	private $html_button_blobs = array();
	public function html_as_button($html_blob) {
		$this->should_render_buttons(true);
		$this->html_button_blobs[] = $html_blob;
	}

	public function icon ($icon, $title, array $attr = array()) {

		$this->should_render_buttons(true);
		$this->buttons[] = array(
			"name" => html::icon($icon) . '<span class="toolbar-icon-label">' .  $title . '</span>',
			"attr" => $attr
		);

	}

	private $tabs = array();
	public function tab ( $title, $attr = false ) {

		if ( !$attr ) $attr = array();

		$this->tabs[ ] = array(
			"name" => $title,
			"attr" => $attr
		);

	}

	private $info = array();
	public function info ( $html ) {

		if ( gettype( $html ) == "string" ) {
			$this->info[ ] = $html;
			return true;
		}

		return false;

	}

	private $menus = array();
	public function menu (Menu_Model $menu, array $settings = array()) {

		$view = new View('menu', array_merge(array(
			"menu" => $menu,
			"class" => "menu main-toolbar-menu",
			"orientation" => "right"
		), $settings));

		$this->menus[] = $view->render();

	}

	private function get_button_html () {

		$h = "";

		foreach ( $this->buttons as $b ) {
			$a = array();
			foreach ( $b[ "attr" ] as $k => $v )
				$a[] = "$k=\"$v\"";
			$h .= "<a " . implode( " ", $a ) . ">" . $b[ "name" ] . "</a>";
		}
		$h .= implode("", $this->html_button_blobs);

		return $h;

	}

	public function render () {

		print '<div class="main-toolbar">';

		if ( gettype( $this->title ) == "string" ) {
			print '<div class="main-toolbar-title">' . $this->title . '</div>';
		}

		if ( gettype( $this->subtitle ) == "string" ) {
			print '<div class="main-toolbar-subtitle">' . $this->subtitle . '</div>';
		} else {
			print '<div class="main-toolbar-subtitle"></div>';
		}

		if ( count( $this->info ) > 0 ) {
			print '<div class="main-toolbar-info">';
			foreach ( $this->info as $html ) print $html;
			print '</div>';
		}

		if (count($this->menus) > 0) {
			foreach ($this->menus as $html) print $html;
		}

		if ($this->should_render_buttons) {
			/* Class main-toolbar-buttons is for styling. Class toolbar-buttons is for locating */
			print '<div class="main-toolbar-buttons toolbar-buttons">';
			print $this->get_button_html();
			print '</div>';
		}

		print '<div class="clear"></div>';
		print '</div>';

	}

}
