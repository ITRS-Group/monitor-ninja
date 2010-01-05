<?php

class CSSPseudoFormRadioGroup extends CSSPropertyHandler {
  function CSSPseudoFormRadioGroup() { 
    $this->CSSPropertyHandler(true, true); 
  }

  function default_value() { 
    return null; 
  }

  function parse($value) { 
    return $value;
  }

  function get_property_code() {
    return CSS_HTML2PS_FORM_RADIOGROUP;
  }

  function get_property_name() {
    return '-html2ps-form-radiogroup';
  }
}

CSS::register_css_property(new CSSPseudoFormRadioGroup);

?>