<?php

class CSSZIndex extends CSSPropertyHandler {
  function CSSZIndex() { 
    $this->CSSPropertyHandler(false, false); 
  }

  function default_value() { return 0; }

  function parse($value) {
    if ($value === 'inherit') { 
      return CSS_PROPERTY_INHERIT;
    };

    return (int)$value;
  }

  function get_property_code() {
    return CSS_Z_INDEX;
  }

  function get_property_name() {
    return 'z-index';
  }
}

CSS::register_css_property(new CSSZIndex);

?>