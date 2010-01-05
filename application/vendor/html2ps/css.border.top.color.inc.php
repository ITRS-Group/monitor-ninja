<?php
// $Header: /cvsroot/html2ps/css.border.top.color.inc.php,v 1.1 2006/09/07 18:38:13 Konstantin Exp $

class CSSBorderTopColor extends CSSSubProperty {
  function CSSBorderTopColor(&$owner) {
    $this->CSSSubProperty($owner);
  }

  function set_value(&$owner_value, &$value) {
    $owner_value->top->setColor($value);
  }

  function get_value(&$owner_value) {
    return $owner_value->top->color->copy();
  }

  function get_property_code() {
    return CSS_BORDER_TOP_COLOR;
  }

  function get_property_name() {
    return 'border-top-color';
  }

  function parse($value) {
    if ($value == 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    return parse_color_declaration($value);
  }
}

?>