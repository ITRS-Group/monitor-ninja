<?php

	/**
	 * Ninja Toolbar Management
	 *
	 *
	 */

	class Toolbar_Controller {

		public $title = false;
		public function __construct ( $title = false ) {

			$this->title = ( gettype( $title ) == "string" ) ? $title: false;

		}

		private $buttons = array();
		public function button ( $title, $attr = false ) {

			if ( !$attr ) $attr = array();

			$this->buttons[ ] = array(
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

		private function get_button_html () {

			$h = "";

			foreach ( $this->buttons as $b ) {
				$a = array();
				foreach ( $b[ "attr" ] as $k => $v )
					$a[] = "$k=\"$v\"";
				$h .= "<button " . implode( " ", $a ) . ">" . $b[ "name" ] . "</button>";
			}

			return $h;

		}

		public function render () {

			print '<div class="main-toolbar">';

			if ( gettype( $this->title ) == "string" ) {
				print '<div class="main-toolbar-title">' . $this->title . '</div>';
			}

			if ( count( $this->info ) > 0 ) {
				print '<div class="main-toolbar-info">';
				foreach ( $this->info as $html ) print $html;
				print '</div>';
			}

			if ( count( $this->buttons ) > 0 ) {
				print '<div class="main-toolbar-buttons">';
				print $this->get_button_html();
				print '</div>';
			}

			print '<div class="clear"></div>';
			print '</div>';

		}

	}