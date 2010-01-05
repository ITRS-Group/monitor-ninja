<?php
// $Header: /cvsroot/html2ps/css.border.left.color.inc.php,v 1.1 2006/09/07 18:38:13 Konstantin Exp $

class CSSBorderLeftColor extends CSSSubProperty {
  function CSSBorderLeftColor(&$owner) {
    $this->CSSSubProperty($owner);
  }

  function set_value(&$owner_value, &$value) {
    $owner_value->left->setColor($value);
  }

  function get_value(&$owner_value) {
    return $owner_value->left->color->copy();
  }

  function get_property_code() {
    return CSS_BORDER_LEFT_COLOR;
  }

  function get_property_name() {
    return 'border-left-color';
  }

  function parse($value) {
    if ($value == 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    return parse_color_declaration($value);
  }
}

?>