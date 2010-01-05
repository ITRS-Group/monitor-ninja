<?php

require_once(HTML2PS_DIR.'value.margin.class.php');

class CSSMargin extends CSSPropertyHandler {
  var $default_value;

  function CSSMargin() { 
    $this->default_value = $this->parse("0");
    $this->CSSPropertyHandler(false, false); 
  }

  function default_value() { 
    return $this->default_value->copy(); 
  }

  function parse_in($value) {
    $values = preg_split('/\s+/', trim($value));

    switch (count($values)) {
    case 1:
      $v1 = $values[0];
      return array($v1, $v1, $v1, $v1);
    case 2:
      $v1 = $values[0];
      $v2 = $values[1];
      return array($v1, $v2, $v1, $v2);
    case 3:
      $v1 = $values[0];
      $v2 = $values[1];
      $v3 = $values[2];
      return array($v1, $v2, $v3, $v2);
    case 4:
      $v1 = $values[0];
      $v2 = $values[1];
      $v3 = $values[2];
      $v4 = $values[3];
      return array($v1, $v2, $v3, $v4);
    default:
      // We newer should get there, because 'margin' value can contain from 1 to 4 widths
      return array(0,0,0,0);
    };
  }

  function parse($value) {
    if ($value === 'inherit') { 
      return CSS_PROPERTY_INHERIT; 
    };

    $value = MarginValue::init($this->parse_in($value));
    return $value;
  }

  function get_property_code() {
    return CSS_MARGIN;
  }

  function get_property_name() {
    return 'margin';
  }
}
   
class CSSMarginTop extends CSSSubFieldProperty {
  function parse($value) { 
    if ($value === 'inherit') { return CSS_PROPERTY_INHERIT; };
    return MarginSideValue::init($value); 
  }

  function get_property_code() {
    return CSS_MARGIN_TOP;
  }

  function get_property_name() {
    return 'margin-top';
  }
}

class CSSMarginRight extends CSSSubFieldProperty {
  function parse($value) { 
    if ($value === 'inherit') { return CSS_PROPERTY_INHERIT; };
    return MarginSideValue::init($value); 
  }

  function get_property_code() {
    return CSS_MARGIN_RIGHT;
  }

  function get_property_name() {
    return 'margin-right';
  }
}

class CSSMarginLeft extends CSSSubFieldProperty {
  function parse($value) { 
    if ($value === 'inherit') { return CSS_PROPERTY_INHERIT; };
    return MarginSideValue::init($value); 
  }

  function get_property_code() {
    return CSS_MARGIN_LEFT;
  }

  function get_property_name() {
    return 'margin-left';
  }
}

class CSSMarginBottom extends CSSSubFieldProperty {
  function parse($value) { 
    if ($value === 'inherit') { return CSS_PROPERTY_INHERIT; };
    return MarginSideValue::init($value); 
  }

  function get_property_code() {
    return CSS_MARGIN_BOTTOM;
  }

  function get_property_name() {
    return 'margin-bottom';
  }
}

$mh = new CSSMargin;
CSS::register_css_property($mh);
CSS::register_css_property(new CSSMarginLeft($mh, 'left'));
CSS::register_css_property(new CSSMarginRight($mh, 'right'));
CSS::register_css_property(new CSSMarginTop($mh, 'top'));
CSS::register_css_property(new CSSMarginBottom($mh, 'bottom'));

?>
