<?php

class CSSPseudoLinkDestination extends CSSPropertyHandler {
  function CSSPseudoLinkDestination() { 
    $this->CSSPropertyHandler(false, false); 
  }

  function default_value() { 
    return null; 
  }

  function parse($value) { 
    return $value;
  }

  function get_property_code() {
    return CSS_HTML2PS_LINK_DESTINATION;
  }

  function get_property_name() {
    return '-html2ps-link-destination';
  }
}

CSS::register_css_property(new CSSPseudoLinkDestination);

?>