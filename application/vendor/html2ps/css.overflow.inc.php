<?php
// $Header: /cvsroot/html2ps/css.overflow.inc.php,v 1.8 2006/09/07 18:38:14 Konstantin Exp $

define('OVERFLOW_VISIBLE',0);
define('OVERFLOW_HIDDEN',1);

class CSSOverflow extends CSSPropertyStringSet {
  function CSSOverflow() { 
    $this->CSSPropertyStringSet(false, 
                                false,
                                array('inherit' => CSS_PROPERTY_INHERIT,
                                      'hidden'  => OVERFLOW_HIDDEN,
                                      'scroll'  => OVERFLOW_HIDDEN,
                                      'auto'    => OVERFLOW_HIDDEN,
                                      'visible' => OVERFLOW_VISIBLE)); 
  }

  function default_value() { 
    return OVERFLOW_VISIBLE; 
  }

  function get_property_code() {
    return CSS_OVERFLOW;
  }

  function get_property_name() {
    return 'overflow';
  }
}

CSS::register_css_property(new CSSOverflow);

?>
