<?php

class TestWhiteSpace extends GenericTest {
  function testWhiteSpace1() {
    $tree = $this->runPipeline(file_get_contents('test.white-space.1.html'));

    $element =& $tree->get_element_by_id('div-normal');
    $this->assertEqual($this->get_line_box_count($element), 
                       15,
                       'Incorrect line box number in DIV with "white-space: normal" [%s]');

    $element =& $tree->get_element_by_id('div-pre');
    $this->assertEqual($this->get_line_box_count($element), 
                       10,
                       'Incorrect line box number in DIV with "white-space: pre" [%s]');

    $element =& $tree->get_element_by_id('div-pre2');
    $this->assertEqual($this->get_line_box_count($element), 
                       9,
                       'Incorrect line box number in DIV with "white-space: pre" without leading linefeeds [%s]');

    $element =& $tree->get_element_by_id('div-pre3');
    $this->assertEqual($this->get_line_box_count($element), 
                       11,
                       'Incorrect line box number in DIV with "white-space: pre" with trailing empty line [%s]');

    $element =& $tree->get_element_by_id('div-nowrap');
    $this->assertEqual($this->get_line_box_count($element), 
                       2,
                       'Incorrect line box number in DIV with "white-space: nowrap" [%s]');

    $element =& $tree->get_element_by_id('div-pre-wrap');
    $this->assertEqual($this->get_line_box_count($element), 
                       21,
                       'Incorrect line box number in DIV with "white-space: pre-wrap" [%s]');

    $element =& $tree->get_element_by_id('div-pre-wrap2');
    $this->assertEqual($this->get_line_box_count($element), 
                       20,
                       'Incorrect line box number in DIV with "white-space: pre-wrap" without leading linefeeds [%s]');

    $element =& $tree->get_element_by_id('div-pre-line');
    $this->assertEqual($this->get_line_box_count($element), 
                       19,
                       'Incorrect line box number in DIV with "white-space: pre-line" with trailing empty line [%s]');
  }

  function testWhiteSpace2() {
    $tree = $this->runPipeline(file_get_contents('test.white-space.2.html'));

    $element =& $tree->get_element_by_id('div-normal');
    $this->assertEqual($this->get_line_box_count($element), 
                       2,
                       'Incorrect line box number in DIV with "white-space: normal" [%s]');

    $element =& $tree->get_element_by_id('div-pre');
    $this->assertEqual($this->get_line_box_count($element), 
                       4,
                       'Incorrect line box number in DIV with "white-space: pre" [%s]');

    $element =& $tree->get_element_by_id('div-pre2');
    $this->assertEqual($this->get_line_box_count($element), 
                       3,
                       'Incorrect line box number in DIV with "white-space: pre" without leading linefeeds [%s]');

    $element =& $tree->get_element_by_id('div-pre3');
    $this->assertEqual($this->get_line_box_count($element), 
                       4,
                       'Incorrect line box number in DIV with "white-space: pre" with trailing empty line [%s]');

    $element =& $tree->get_element_by_id('div-nowrap');
    $this->assertEqual($this->get_line_box_count($element), 
                       1,
                       'Incorrect line box number in DIV with "white-space: nowrap" [%s]');

    $element =& $tree->get_element_by_id('div-pre-wrap');
    $this->assertEqual($this->get_line_box_count($element), 
                       2,
                       'Incorrect line box number in DIV with "white-space: pre-wrap" [%s]');

    $element =& $tree->get_element_by_id('div-pre-wrap2');
    $this->assertEqual($this->get_line_box_count($element), 
                       2,
                       'Incorrect line box number in DIV with "white-space: pre-wrap" without leading linefeeds [%s]');

    $element =& $tree->get_element_by_id('div-pre-line');
    $this->assertEqual($this->get_line_box_count($element), 
                       2,
                       'Incorrect line box number in DIV with "white-space: pre-line" with trailing empty line [%s]');
  }

  function get_line_box_count(&$box) {
    $line_box_count = 0;
    $prevous_br = false;
    foreach ($box->content as $child) {
      if (is_a($child, 'InlineBox')) {
        $line_box_count += $child->get_line_box_count();

        $last_box =& $child->get_last();
        $previous_br = is_a($last_box, 'BRBox');
      } elseif (is_a($child, 'BRBox')) {
        if ($previous_br) { 
          $line_box_count++;
        };
        $previous_br = true;
      } else {
        $previous_br = false;
      };
    };
    return $line_box_count;
  }
}

?>