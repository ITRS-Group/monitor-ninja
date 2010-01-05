<?php

class CSSOrphans extends CSSPropertyHandler {
  function CSSOrphans() { 
    $this->CSSPropertyHandler(true, false); 
  }

  function default_value() { 
    return 2; 
  }

  function parse($value) {
    return (int)$value;
  }

  function get_property_code() {
    return CSS_ORPHANS;
  }

  function get_property_name() {
    return 'orphans';
  }
}

CSS::register_css_property(new CSSOrphans);

?>