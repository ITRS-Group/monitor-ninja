<?php

class CSSPseudoFormAction extends CSSPropertyHandler {
  function CSSPseudoFormAction() { $this->CSSPropertyHandler(true, true); }

  function default_value() { return null; }

  function parse($value) { 
    return $value;
  }

  function get_property_code() {
    return CSS_HTML2PS_FORM_ACTION;
  }

  function get_property_name() {
    return '-html2ps-form-action';
  }
}

CSS::register_css_property(new CSSPseudoFormAction);

?>