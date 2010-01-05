<?php

class TestTextareaHeight extends GenericTest {
  function TestTextareaHeight1() {
    $tree = $this->runPipeline('
<textarea id="textarea" style="height: 8cm;">TEXT</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $this->assertEqual($element->get_full_height(), 
                       mm2pt(80));
  }
}

?>