<?php

class TestWidthAbsolutePositioned extends GenericTest {
  function testWidthAbsolutePositioned1() {
    $media = null;
    $pipeline = null;
    $tree = $this->runPipeline(file_get_contents('test.width.absolute.positioned.1.html'), 
                               $media,
                               $pipeline);

    $font_size = $tree->getCSSProperty(CSS_FONT_SIZE);
    $base = $font_size->getPoints();

    $element =& $tree->get_element_by_id('div1');
    $this->assertWithinMargin($element->get_width(), 
                              $pipeline->output_driver->stringwidth('No positioning data at all', 'Times-Roman', 'iso-8859-1', $base),
                              0);

    $element =& $tree->get_element_by_id('div2');
    $this->assertWithinMargin($element->get_width(), 
                              $pipeline->output_driver->stringwidth('Left', 'Times-Roman', 'iso-8859-1', $base),
                              0);
    
    $element =& $tree->get_element_by_id('div3');
    $this->assertEqual($element->get_width(),
                       mm2pt($media->real_width()) - px2pt(200));

    $element =& $tree->get_element_by_id('div4');
    $this->assertEqual($element->get_width(),
                       px2pt(100));

    $element =& $tree->get_element_by_id('div5');
    $this->assertEqual($element->get_width(),
                       px2pt(100));

    $element =& $tree->get_element_by_id('div6');
    $this->assertEqual($element->get_width(),
                       px2pt(100));

    $element =& $tree->get_element_by_id('div7');
    $this->assertEqual($element->get_width(),
                       px2pt(100));

    $element =& $tree->get_element_by_id('div8');
    $this->assertWithinMargin($element->get_width(), 
                              $pipeline->output_driver->stringwidth('Right', 'Times-Roman', 'iso-8859-1', $base),
                              0);

    $element =& $tree->get_element_by_id('div9');
    $this->assertEqual($element->get_width(),
                       mm2pt($media->real_width()) - px2pt(100),
                       'DIV with long text and "left" property has incorrect width [%s]');

    $element =& $tree->get_element_by_id('div10');
    $this->assertEqual($element->get_width(),
                       mm2pt($media->real_width()) - px2pt(100),
                       'DIV with long text and "right" property has incorrect width [%s]');

    $element =& $tree->get_element_by_id('div11');
    $this->assertEqual($element->get_width(),
                       mm2pt($media->real_width()),
                       'DIV with long text and no positioning properties has incorrect width [%s]');

    $element =& $tree->get_element_by_id('div12');
    $this->assertEqual($element->get_width(),
                       mm2pt($media->real_width()) - px2pt(200),
                       'DIV with long text and both "left" and "right" properties has incorrect width [%s]');
  }
}

?>