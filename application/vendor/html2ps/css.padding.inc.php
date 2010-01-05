<?php

require_once(HTML2PS_DIR.'value.padding.class.php');

class CSSPadding extends CSSPropertyHandler {
  var $default_value;

  function CSSPadding() { 
    $this->default_value = $this->parse("0");
    $this->CSSPropertyHandler(false, false); 
  }

  function default_value() { return $this->default_value->copy(); }

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
      // We newer should get there, because 'padding' value can contain from 1 to 4 widths
      return array(0,0,0,0);
    };
  }

  function parse($string) {
    if ($string === 'inherit') {
      return CSS_PROPERTY_INHERIT;
    };

    $values = $this->parse_in($string);
    $padding = PaddingValue::init($values);

    return $padding;
  }

  function get_property_code() {
    return CSS_PADDING;
  }

  function get_property_name() {
    return 'padding';
  }
}
   
class CSSPaddingTop extends CSSSubFieldProperty {
  function parse($value) { 
    if ($value === 'inherit') { return CSS_PROPERTY_INHERIT; };
    return PaddingSideValue::init($value); 
  }

  function get_property_code() {
    return CSS_PADDING_TOP;
  }

  function get_property_name() {
    return 'padding-top';
  }
}

class CSSPaddingRight extends CSSSubFieldProperty {
  function parse($value) { 
    if ($value === 'inherit') { return CSS_PROPERTY_INHERIT; };
    $result = PaddingSideValue::init($value);     
    return $result;
  }

  function get_property_code() {
    return CSS_PADDING_RIGHT;
  }

  function get_property_name() {
    return 'padding-right';
  }
}

class CSSPaddingLeft extends CSSSubFieldProperty {
  function parse($value) { 
    if ($value === 'inherit') { return CSS_PROPERTY_INHERIT; };
    return PaddingSideValue::init($value); 
  }

  function get_property_code() {
    return CSS_PADDING_LEFT;
  }

  function get_property_name() {
    return 'padding-left';
  }
}

class CSSPaddingBottom extends CSSSubFieldProperty {
  function parse($value) { 
    if ($value === 'inherit') { 
      return CSS_PROPERTY_INHERIT; 
    };

    return PaddingSideValue::init($value); 
  }

  function get_property_code() {
    return CSS_PADDING_BOTTOM;
  }

  function get_property_name() {
    return 'padding-bottom';
  }
}

$ph = new CSSPadding;
CSS::register_css_property($ph);
CSS::register_css_property(new CSSPaddingLeft($ph,   'left'));
CSS::register_css_property(new CSSPaddingRight($ph,  'right'));
CSS::register_css_property(new CSSPaddingTop($ph,    'top'));
CSS::register_css_property(new CSSPaddingBottom($ph, 'bottom'));

?>
