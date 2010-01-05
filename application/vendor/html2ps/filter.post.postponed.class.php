<?php

class PostTreeFilterPostponed extends PreTreeFilter {
  var $_driver;

  function PostTreeFilterPostponed(&$driver) {
    $this->_driver  =& $driver;
  }

  function process(&$tree, $data, &$pipeline) {
    if (is_a($tree, 'GenericContainerBox')) {
      for ($i=0; $i<count($tree->content); $i++) {
        $position = $tree->content[$i]->get_css_property(CSS_POSITION);
        $float    = $tree->content[$i]->get_css_property(CSS_FLOAT);

        if ($position == POSITION_RELATIVE) {
          $this->_driver->postpone($tree->content[$i]);
        } elseif ($float != FLOAT_NONE) {
          $this->_driver->postpone($tree->content[$i]);
        };

        $this->process($tree->content[$i], $data, $pipeline);
      };
    };

    return true;
  }
}
?>