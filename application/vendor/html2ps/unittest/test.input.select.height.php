<?php

class TestInputSelectHeight extends GenericTest {
  function TestInputSelectHeight1() {
    $tree = $this->runPipeline('
<select id="input" style="height: 33pt;"></select>
');

    $element =& $tree->get_element_by_id('input');
    $this->assertEqual($element->get_full_height(), 
                       pt2pt(33));
  }

  function TestInputSelectHeight2() {
    $tree = $this->runPipeline('
<style>
* { font-size: 33pt; }
</style>
<select id="input"></select>
');

    $element =& $tree->get_element_by_id('input');
    $this->assertEqual($element->get_full_height(), 
                       pt2pt(33) + $element->_get_vert_extra());
  }
}

?>