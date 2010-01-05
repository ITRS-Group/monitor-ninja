<?php

class CSSPropertyHandler {
  var $_inheritable;
  var $_inheritable_text;

  function css($value, &$pipeline) { 
    $css_state =& $pipeline->get_current_css_state();

    if ($this->applicable($css_state)) {
      $this->replace($this->parse($value, $pipeline), $css_state); 
    };
  }

  function applicable($css_state) {
    return true;
  }

  function clearDefaultFlags(&$state) {
    $state->set_propertyDefaultFlag($this->get_property_code(), false);
  }

  function CSSPropertyHandler($inheritable, $inheritable_text) { 
    $this->_inheritable = $inheritable;
    $this->_inheritable_text = $inheritable_text;
  }

  /**
   * Optimization: this function is called very often, so 
   * we minimize the overhead by calling $this->get_property_code()
   * once per property handler object instead of calling in every
   * CSSPropertyHandler::get() call.
   */
  function &get(&$state) { 
    static $property_code = null;
    if (is_null($property_code)) {
      $property_code = $this->get_property_code();
    };

    if (!isset($state[$property_code])) {
      $null = null;
      return $null;
    };

    return $state[$property_code];
  }

  function inherit($old_state, &$new_state) { 
    $code = $this->get_property_code();
    $new_state[$code] = ($this->_inheritable ? 
                         $old_state[$code] : 
                         $this->default_value());
  }

  function isInheritableText() { 
    return $this->_inheritable_text;
  }

  function isInheritable() {
    return $this->_inheritable;
  }

  function inherit_text($old_state, &$new_state) { 
    $code = $this->get_property_code();

    if ($this->_inheritable_text) {
      $new_state[$code] = $old_state[$code];
    } else {
      $new_state[$code] = $this->default_value();
    };
  }

  function is_default($value) { 
    if (is_object($value)) {
      return $value->is_default();
    } else {
      return $this->default_value() === $value; 
    };
  }

  function is_subproperty() { return false; }

  function replace($value, &$state) { 
    $state->set_property($this->get_property_code(), $value);
  }

  function replaceDefault($value, &$state) { 
    $state->set_propertyDefault($this->get_property_code(), $value);
  }

  function replace_array($value, &$state) {
    static $property_code = null;
    if (is_null($property_code)) {
      $property_code = $this->get_property_code();
    };

    $state[$property_code] = $value;
  }
}

?>