<?php

class TestTextareaWrap extends GenericTest {
  function TestTextareaWrap1() {
    $tree = $this->runPipeline('
<textarea id="textarea" style="border: solid black 1px; width: 8cm;">TEXT</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $inline_content =& $element->content[0];
    $this->assertEqual($inline_content->get_line_box_count(), 
                       1);
  }

  function TestTextareaWrap2() {
    $tree = $this->runPipeline('
<textarea id="textarea" style="border: solid black 1px; width: 8cm;">
TEXT
TEXT</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $inline_content =& $element->content[0];
    $this->assertEqual($inline_content->get_line_box_count(), 
                       2);
  }

  function TestTextareaWrap3() {
    $tree = $this->runPipeline('
<textarea id="textarea" style="border: solid black 1px; width: 8cm;">
TEXT1
TEXT2 TEXT
TEXT3 TEXT TEXT TEXT TEXT TEXT TEXT TEXT TEXT TEXT TEXT TEXT TEXT TEXT TEXT TEXT
</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $inline_content =& $element->content[0];

    $this->assertEqual($inline_content->get_line_box_count(), 
                       6);
  }

  function TestTextareaWrap4() {
    $tree = $this->runPipeline('
<textarea id="textarea" style="border: solid black 1px; width: 8cm;">
TEXT
</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $inline_content =& $element->content[0];
    $this->assertEqual($inline_content->get_line_box_count(), 
                       2);
  }
}

?>