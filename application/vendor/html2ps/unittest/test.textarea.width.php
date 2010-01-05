<?php

class TestTextareaWidth extends GenericTest {
  function TestTextareaWidth1() {
    $tree = $this->runPipeline('
<textarea id="textarea" style="width: 8cm;">TEXT</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $this->assertEqual($element->get_full_width(), 
                       mm2pt(80));
  }
}

?>