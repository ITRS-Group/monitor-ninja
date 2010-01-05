<?php

class TestCSSPageBreakAfter extends GenericTest {
  function testCSSPageBreakAfter1() {
    $tree = $this->runPipeline(file_get_contents('test.css.page.break.after.1.html'));
    $div = $tree->get_element_by_id('div');
    $this->assertEqual(PAGE_BREAK_AVOID, $div->getCSSProperty(CSS_PAGE_BREAK_AFTER));
  }

  function testCSSPageBreakAfter2() {
    $tree = $this->runPipeline(file_get_contents('test.css.page.break.after.2.html'),
                               $media);
    $page_heights = PageBreakLocator::getPages($tree, 
                                               mm2pt($media->real_height()), 
                                               mm2pt($media->height() - $media->margins['top']));

    $this->assertEqual(count($page_heights), 2);

    $div = $tree->get_element_by_id('div');
    $h1 = $tree->get_element_by_id('h1');

    $this->assertEqual($page_heights[0], $div->get_full_height());
  }
}

?>