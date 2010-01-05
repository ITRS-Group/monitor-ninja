<?php

class TestCSSParseAtRules extends GenericTest {
  function testCSSParseAtRulesSimple() {
    $pipeline =& PipelineFactory::create_default_pipeline('utf-8', 'test.pdf');
    $new_css_content =& parse_css_atpage_rules('body { background-color: green; } @page { background-color: red; } #test { border: none; }', $pipeline);

    $this->assertEqual($new_css_content, 'body { background-color: green; } #test { border: none; }');

    $color =& $pipeline->_page_at_rules[CSS_PAGE_SELECTOR_ALL][0]->css->getPropertyValue(CSS_BACKGROUND_COLOR);
    $this->assertNotNull($color);
    $this->assertEqual($color->r, 1);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }

  function testCSSParseAtRulesNested() {
    $pipeline =& PipelineFactory::create_default_pipeline('utf-8', 'test.pdf');
    $new_css_content =& parse_css_atpage_rules('body { background-color: green; } @page { @top-left { background-color: lime; } } #test { border: none; }', $pipeline);

    $this->assertEqual($new_css_content, 'body { background-color: green; } #test { border: none; }');

    $color =& $pipeline->_page_at_rules[CSS_PAGE_SELECTOR_ALL][0]->margin_boxes[CSS_MARGIN_BOX_SELECTOR_TOP_LEFT]->css->body->getPropertyValue(CSS_BACKGROUND_COLOR);
    $this->assertNotNull($color);
    $this->assertEqual($color->r, 0);
    $this->assertEqual($color->g, 1);
    $this->assertEqual($color->b, 0);
  }

  function testCSSParseAtRulesNestedContent() {
    $pipeline =& PipelineFactory::create_default_pipeline('utf-8', 'test.pdf');
    $new_css_content =& parse_css_atpage_rules('body { background-color: green; } @page { @top-left { content: "TEXT"; } } #test { border: none; }', $pipeline);

    $this->assertEqual($new_css_content, 'body { background-color: green; } #test { border: none; }');

    $content =& $pipeline->_page_at_rules[CSS_PAGE_SELECTOR_ALL][0]->margin_boxes[CSS_MARGIN_BOX_SELECTOR_TOP_LEFT]->css->body->getPropertyValue(CSS_CONTENT);
    $this->assertNotNull($content);
    $this->assertEqual($content->render(new CSSCounterCollection()), "TEXT");
  }
}

?>