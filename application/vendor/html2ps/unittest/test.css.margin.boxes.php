<?php

class TestCSSParseMarginBoxes extends GenericTest {
  function testCSSParseMarginBoxesTopLeftCornerSize() {
    parse_config_file('../html2ps.config');
    $media =& Media::predefined('A4');
    $media->set_margins(array('left' => 10,
                              'top' => 10,
                              'right' => 10,
                              'bottom' => 10));

    $pipeline =& PipelineFactory::create_default_pipeline('utf-8', 'test.pdf');
    $pipeline->_setupScales($media);
    $pipeline->_cssState = array(new CSSState(CSS::get()));

    $boxes = $pipeline->reflow_margin_boxes(1, $media);

    $box =& $boxes[CSS_MARGIN_BOX_SELECTOR_TOP_LEFT_CORNER];
    $this->assertEqual($box->get_width(), mm2pt(10));
    $this->assertEqual($box->get_height(), mm2pt(10));
  }

  function testCSSParseMarginBoxesTopLeftSizeNoContent() {
    parse_config_file('../html2ps.config');
    $media =& Media::predefined('A4');
    $media->set_margins(array('left' => 10,
                              'top' => 10,
                              'right' => 10,
                              'bottom' => 10));

    $pipeline =& PipelineFactory::create_default_pipeline('utf-8', 'test.pdf');
    $pipeline->_setupScales($media);
    $pipeline->_cssState = array(new CSSState(CSS::get()));

    $boxes = $pipeline->reflow_margin_boxes(1, $media);

    $box =& $boxes[CSS_MARGIN_BOX_SELECTOR_TOP_LEFT];
    $this->assertEqual($box->get_width(), mm2pt(0));
    $this->assertEqual($box->get_height(), mm2pt(10));
  }

  function testCSSParseMarginBoxesTopLeftSize() {
    parse_config_file('../html2ps.config');
    $media =& Media::predefined('A4');
    $media->set_margins(array('left' => 10,
                              'top' => 10,
                              'right' => 10,
                              'bottom' => 10));

    $pipeline =& PipelineFactory::create_default_pipeline('utf-8', 'test.pdf');
    $pipeline->_prepare($media);
    $pipeline->_cssState = array(new CSSState(CSS::get()));
    parse_css_atpage_rules('@page { @top-left { content: "TEXT"; } }', $pipeline);

    $boxes = $pipeline->reflow_margin_boxes(1, $media);

    $box =& $boxes[CSS_MARGIN_BOX_SELECTOR_TOP_LEFT];
    $this->assertNotEqual($box->get_width(), 0);
    $expected_width = $pipeline->output_driver->stringwidth('TEXT', 'Times-Roman', 'iso-8859-1', 12);
    $this->assertTrue($box->get_width() >= $expected_width);
    $this->assertEqual($box->get_height(), mm2pt(10));
  }
}

?>