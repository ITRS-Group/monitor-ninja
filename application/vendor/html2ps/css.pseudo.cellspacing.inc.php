<?php
// $Header: /cvsroot/html2ps/css.pseudo.cellspacing.inc.php,v 1.6 2006/09/07 18:38:14 Konstantin Exp $

class CSSCellSpacing extends CSSPropertyHandler {
  function CSSCellSpacing() { 
    $this->CSSPropertyHandler(true, false); 
  }

  function default_value() { 
    return Value::fromData(1, UNIT_PX);
  }

  function parse($value) { 
    return Value::fromString($value);
  }

  function get_property_code() {
    return CSS_HTML2PS_CELLSPACING;
  }

  function get_property_name() {
    return '-html2ps-cellspacing';
  }
}

CSS::register_css_property(new CSSCellSpacing);

?>