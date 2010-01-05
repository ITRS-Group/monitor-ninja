<?php

class TestTagOlType extends GenericTest {
  function testTagOlType1() {
    $tree = $this->runPipeline(file_get_contents('test.tag.ol.type.html'));

    $ol =& $tree->get_element_by_id('ol_1');
    $this->assertEqual(LST_DECIMAL, $ol->getCSSProperty(CSS_LIST_STYLE_TYPE));

    $ol =& $tree->get_element_by_id('ol_a');
    $this->assertEqual(LST_LOWER_LATIN, $ol->getCSSProperty(CSS_LIST_STYLE_TYPE));

    $ol =& $tree->get_element_by_id('ol_A');
    $this->assertEqual(LST_UPPER_LATIN, $ol->getCSSProperty(CSS_LIST_STYLE_TYPE));

    $ol =& $tree->get_element_by_id('ol_i');
    $this->assertEqual(LST_LOWER_ROMAN, $ol->getCSSProperty(CSS_LIST_STYLE_TYPE));

    $ol =& $tree->get_element_by_id('ol_I');
    $this->assertEqual(LST_UPPER_ROMAN, $ol->getCSSProperty(CSS_LIST_STYLE_TYPE));
  }
}

?>