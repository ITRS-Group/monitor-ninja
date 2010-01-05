<?php
// $Header: /cvsroot/html2ps/css.color.inc.php,v 1.13 2007/01/24 18:55:51 Konstantin Exp $

class CSSPage extends CSSPropertyHandler {
  function CSSPage() { 
    $this->CSSPropertyHandler(true, true); 
  }

  function default_value() { 
    return 'auto'; 
  }

  function parse($value) {
    return $value;
  }

  function get_property_code() {
    return CSS_PAGE;
  }

  function get_property_name() {
    return 'page';
  }
}

CSS::register_css_property(new CSSPage());

?>