<?php
// $Header: /cvsroot/html2ps/css.pseudo.listcounter.inc.php,v 1.4 2006/09/07 18:38:14 Konstantin Exp $

class CSSPseudoListCounter extends CSSPropertyHandler {
  function CSSPseudoListCounter() { 
    $this->CSSPropertyHandler(true, false); 
  }

  function default_value() { 
    return 0; 
  }

  function get_property_code() {
    return CSS_HTML2PS_LIST_COUNTER;
  }

  function get_property_name() {
    return '-html2ps-list-counter';
  }

  function parse($value) {
    return (int)$value;
  }
}

CSS::register_css_property(new CSSPseudoListCounter);

?>