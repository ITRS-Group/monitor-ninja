<?php

class TestInputTextHeight extends GenericTest {
  function TestInputTextHeight1() {
    $tree = $this->runPipeline('
<input id="input" style="height: 22pt;" type="text"/>
');

    $element =& $tree->get_element_by_id('input');
    $this->assertEqual($element->get_full_height(), 
                       pt2pt(22));
  }

  function TestInputTextHeight2() {
    $tree = $this->runPipeline('
<style>
* { font-size: 22pt; }
</style>
<input id="input" type="text"/>
');

    $element =& $tree->get_element_by_id('input');
    $this->assertEqual($element->get_full_height(), 
                       pt2pt(22) + $element->_get_vert_extra());
  }
}

?>