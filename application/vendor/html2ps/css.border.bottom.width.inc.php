<?php
// $Header: /cvsroot/html2ps/css.border.bottom.width.inc.php,v 1.2 2007/02/04 17:08:18 Konstantin Exp $

class CSSBorderBottomWidth extends CSSSubProperty {
  function CSSBorderBottomWidth(&$owner) {
    $this->CSSSubProperty($owner);
  }

  function set_value(&$owner_value, &$value) {
    if ($value != CSS_PROPERTY_INHERIT) {
      $owner_value->bottom->width = $value->copy();
    } else {
      $owner_value->bottom->width = $value;
    };
  }

  function get_value(&$owner_value) {
    return $owner_value->bottom->width;
  }

  function get_property_code() {
    return CSS_BORDER_BOTTOM_WIDTH;
  }

  function get_property_name() {
    return 'border-bottom-width';
  }

  function parse($value) {
    if ($value == 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    $width_handler = CSS::get_handler(CSS_BORDER_WIDTH);
    $width = $width_handler->parse_value($value);
    return $width;
  }
}

?>