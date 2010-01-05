<?php

class TestCSSPageBreakBefore extends GenericTest {
  function testCSSPageBreakBefore1() {
    $tree = $this->runPipeline(file_get_contents('test.css.page.break.before.1.html'));

    $div = $tree->get_element_by_id('div');

    $this->assertEqual(PAGE_BREAK_AVOID, $div->getCSSProperty(CSS_PAGE_BREAK_BEFORE));
  }
}

?>