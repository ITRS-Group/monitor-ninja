<?php
// $Header: /cvsroot/html2ps/css.border.left.style.inc.php,v 1.1 2006/09/07 18:38:13 Konstantin Exp $

class CSSBorderLeftStyle extends CSSSubProperty {
  function CSSBorderLeftStyle(&$owner) {
    $this->CSSSubProperty($owner);
  }

  function set_value(&$owner_value, &$value) {
    $owner_value->left->style = $value;
  }

  function get_value(&$owner_value) {
    return $owner_value->left->style;
  }

  function get_property_code() {
    return CSS_BORDER_LEFT_STYLE;
  }

  function get_property_name() {
    return 'border-left-style';
  }

  function parse($value) {
    if ($value == 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    return CSSBorderStyle::parse_style($value);
  }
}

?>