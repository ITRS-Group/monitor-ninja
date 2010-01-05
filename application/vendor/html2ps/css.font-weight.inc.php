<?php

class CSSFontWeight extends CSSSubFieldProperty {
  function default_value() {
    return WEIGHT_NORMAL;
  }

  function parse($value) {
    switch (trim(strtolower($value))) {
    case 'inherit':
      return CSS_PROPERTY_INHERIT;
    case 'bold':
    case '700':
    case '800':
    case '900':
    case 'bolder':
      return WEIGHT_BOLD;
    case 'lighter':
    case 'normal':
    case '100':
    case '200':
    case '300':
    case '400':
    case '500':
    case '600':
    default:
      return WEIGHT_NORMAL;
    };
  }

  function get_property_code() {
    return CSS_FONT_WEIGHT;
  }

  function get_property_name() {
    return 'font-weight';
  }
}

?>