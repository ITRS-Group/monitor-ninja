<?php

class TestTextareaContent extends GenericTest {
  function TestTextareaContent1() {
    $tree = $this->runPipeline('
<textarea id="textarea">sample Textarea content</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $this->assertEqual($element->get_value(), 
                       'sample Textarea content');
  }

  function TestTextareaContent2() {
    $tree = $this->runPipeline('
<textarea id="textarea">
sample Textarea content
</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $this->assertEqual($element->get_value(), 
                       "\nsample Textarea content\n");
  }

  function TestTextareaContent3() {
    $tree = $this->runPipeline('
<textarea id="textarea">&lt;&gt;&amp;</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $this->assertEqual($element->get_value(), 
                       '<>&');
  }

  function TestTextareaContent4() {
    $tree = $this->runPipeline('
<textarea id="textarea"><>&</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $this->assertEqual($element->get_value(), 
                       '<>&');
  }

  function TestTextareaContent5() {
    $tree = $this->runPipeline('
<textarea id="textarea">text<br/>text</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $this->assertEqual($element->get_value(), 
                       'text<br/>text');
  }

  function TestTextareaContent6() {
    $tree = $this->runPipeline('
<textarea id="textarea">text<br/>text&lt;br/&gt;</textarea>
');

    $element =& $tree->get_element_by_id('textarea');
    $this->assertEqual($element->get_value(), 
                       'text<br/>text<br/>');
  }
}

?>