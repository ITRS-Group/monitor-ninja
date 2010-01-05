<?php

class TestBorderBottom extends GenericTest {
  function testBorderBottom1() {
    $tree = $this->runPipeline(file_get_contents('test.border-bottom.1.html'));

    $element =& $tree->get_element_by_id('div1');
    $border =& $element->getCSSProperty(CSS_BORDER);
    $top =& $border->get_top();
    $this->assertEqual($top->get_style(), BS_NONE);
    $left =& $border->get_left();
    $this->assertEqual($left->get_style(), BS_NONE);
    $right =& $border->get_right();
    $this->assertEqual($right->get_style(), BS_NONE);
    $bottom =& $border->get_bottom();
    $this->assertEqual($bottom->get_style(), BS_SOLID);
    $width =& $bottom->get_width();
    $this->assertEqual($width, px2pt(1));
    $color =& $bottom->get_color();
    $this->assertEqual($color->r, 0);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);

    $element =& $tree->get_element_by_id('div2');
    $border =& $element->getCSSProperty(CSS_BORDER);
    $top =& $border->get_top();
    $this->assertEqual($top->get_style(), BS_NONE);
    $left =& $border->get_left();
    $this->assertEqual($left->get_style(), BS_NONE);
    $right =& $border->get_right();
    $this->assertEqual($right->get_style(), BS_NONE);
    $bottom =& $border->get_bottom();
    $this->assertEqual($bottom->get_style(), BS_SOLID);
    $width =& $bottom->get_width();
    $this->assertEqual($width, px2pt(1));
    $color =& $bottom->get_color();
    $this->assertEqual($color->r, 0);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }
}

?>