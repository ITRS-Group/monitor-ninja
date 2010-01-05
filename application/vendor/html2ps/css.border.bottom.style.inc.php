<?php
// $Header: /cvsroot/html2ps/css.border.bottom.style.inc.php,v 1.1 2006/09/07 18:38:13 Konstantin Exp $

class CSSBorderBottomStyle extends CSSSubProperty {
  function CSSBorderBottomStyle(&$owner) {
    $this->CSSSubProperty($owner);
  }

  function set_value(&$owner_value, &$value) {
    $owner_value->bottom->style = $value;
  }

  function get_value(&$owner_value) {
    return $owner_value->bottom->style;
  }

  function get_property_code() {
    return CSS_BORDER_BOTTOM_STYLE;
  }

  function get_property_name() {
    return 'border-bottom-style';
  }

  function parse($value) {
    if ($value == 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    return CSSBorderStyle::parse_style($value);
  }
}

?>