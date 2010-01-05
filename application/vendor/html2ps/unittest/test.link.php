<?php

class TestLink extends GenericTest {
  function testLinkNormal() {
    $tree = $this->runPipeline(file_get_contents('test.link.1.html'));

    $element =& $tree->get_element_by_id('div1');

    $color =& $element->getCSSProperty(CSS_COLOR);
    $this->assertEqual($color->r, 1);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }

  function testLinkAutofix() {
    $tree = $this->runPipeline(file_get_contents('test.link.2.html'));

    $element =& $tree->get_element_by_id('div1');

    $color =& $element->getCSSProperty(CSS_COLOR);
    $this->assertEqual($color->r, 1);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }
}


?>