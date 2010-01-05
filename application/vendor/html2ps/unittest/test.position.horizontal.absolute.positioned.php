<?php

class TestPositionHorizontalAbsolutePositioned extends GenericTest {
  function testPositionHorizontalbsolutePositioned1() {
    $media = null;
    $pipeline = null;
    $tree = $this->runPipeline(file_get_contents('test.position.horizontal.absolute.positioned.1.html'), 
                               $media,
                               $pipeline);

    $font_size = $tree->getCSSProperty(CSS_FONT_SIZE);
    $base = $font_size->getPoints();

    $element =& $tree->get_element_by_id('div1');
    $this->assertEqual($element->get_left(),
                       mm2pt($media->margins['left']),
                       'DIV with no positioning is positioned incorrectly [%s]');

    $element =& $tree->get_element_by_id('div2');
    $this->assertEqual($element->get_left(),
                       mm2pt($media->margins['left']) + px2pt(100),
                       'DIV with "left" property is positioned incorrectly [%s]');
    
    $element =& $tree->get_element_by_id('div3');
    $this->assertEqual($element->get_right(),
                       mm2pt($media->width() - $media->margins['right']) - px2pt(100),
                       'DIV with "right" property is positioned incorrectly [%s]');

    $element =& $tree->get_element_by_id('div4');
    $this->assertEqual($element->get_left(),
                       mm2pt($media->margins['left']),
                       'DIV with long text and "right" property is positioned incorrectly [%s]');

    $element =& $tree->get_element_by_id('div5');
    $this->assertEqual($element->get_left(),
                       mm2pt($media->margins['left']) + px2pt(100),
                       'DIV with long text and "left" property is positioned incorrectly [%s]');
  }
}

?>