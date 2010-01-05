<?php

class TestTagUlType extends GenericTest {
  function testTagUlType1() {
    $tree = $this->runPipeline(file_get_contents('test.tag.ul.type.html'));

    $ul =& $tree->get_element_by_id('ul_disc');
    $this->assertEqual(LST_DISC, $ul->getCSSProperty(CSS_LIST_STYLE_TYPE));

    $ul =& $tree->get_element_by_id('ul_circle');
    $this->assertEqual(LST_CIRCLE, $ul->getCSSProperty(CSS_LIST_STYLE_TYPE));

    $ul =& $tree->get_element_by_id('ul_square');
    $this->assertEqual(LST_SQUARE, $ul->getCSSProperty(CSS_LIST_STYLE_TYPE));
  }
}

?>