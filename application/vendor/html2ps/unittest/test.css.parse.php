<?php

class TestCSSParse extends GenericTest {
  function testCSSParsePropertyUpperCase() {
    $null = null;
    $collection =& parse_css_property('VISIBILITY: hidden;', $null);
    $this->assertTrue($collection->contains(CSS_VISIBILITY));   
    $this->assertEqual($collection->getPropertyValue(CSS_VISIBILITY), VISIBILITY_HIDDEN);
  }

  function testCSSParsePropertyMixedCase() {
    $null = null;
    $collection =& parse_css_property('VISibiLitY: hidden;', $null);
    $this->assertTrue($collection->contains(CSS_VISIBILITY));   
    $this->assertEqual($collection->getPropertyValue(CSS_VISIBILITY), VISIBILITY_HIDDEN);
  }

  function testCSSParseProperty() {
    $null = null;
    $collection =& parse_css_property('-html2ps-html-content: "Sample;Text";', $null);
    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));   

    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "Sample;Text");
  }

  function testCSSParsePropertyWithoutTrailingSemicolon() {
    $null = null;
    $collection =& parse_css_property('content: "TEXT"', $null);
    $this->assertTrue($collection->contains(CSS_CONTENT));   

    $content =& $collection->getPropertyValue(CSS_CONTENT);
    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "TEXT");
  }

  function testCSSParsePropertyMultipart() {
    $null = null;
    $collection =& parse_css_property('-html2ps-html-content: "Double Quoted String" \'Single Quoted String\';', $null);

    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));
   
    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "Double Quoted StringSingle Quoted String");
  }

  function testCSSParseProperties() {
    $null = null;
    $collection =& parse_css_properties('font-weight: bold; -html2ps-html-content: "Sample;Text"; color: red;', $null);

    $properties = $collection->getPropertiesRaw();   
    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));
    $this->assertTrue($collection->contains(CSS_COLOR));
    $this->assertTrue($collection->contains(CSS_FONT_WEIGHT));
    
    $this->assertEqual($collection->getPropertyValue(CSS_FONT_WEIGHT), WEIGHT_BOLD);

    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "Sample;Text");

    $color = $collection->getPropertyValue(CSS_COLOR);
    $this->assertEqual($color->r, 1);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }

  function testCSSParsePropertiesMultiline() {
    $null = null;
    $collection =& parse_css_properties('font-weight: bold;
-html2ps-html-content: "Sample;Text"; 
color: red;', $null);

    $properties = $collection->getPropertiesRaw();   
    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));
    $this->assertTrue($collection->contains(CSS_COLOR));
    $this->assertTrue($collection->contains(CSS_FONT_WEIGHT));
    
    $this->assertEqual($collection->getPropertyValue(CSS_FONT_WEIGHT), WEIGHT_BOLD);

    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "Sample;Text");

    $color = $collection->getPropertyValue(CSS_COLOR);
    $this->assertEqual($color->r, 1);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }

  function testCSSParsePropertiesMultilineWithoutTrailingSemicolon() {
    $null = null;
    $collection =& parse_css_properties('font-weight: bold;
-html2ps-html-content: "Sample;Text"; 
color: red', $null);

    $properties = $collection->getPropertiesRaw();   
    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));
    $this->assertTrue($collection->contains(CSS_COLOR));
    $this->assertTrue($collection->contains(CSS_FONT_WEIGHT));
    
    $this->assertEqual($collection->getPropertyValue(CSS_FONT_WEIGHT), WEIGHT_BOLD);

    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "Sample;Text");

    $color = $collection->getPropertyValue(CSS_COLOR);
    $this->assertEqual($color->r, 1);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }

  function testCSSParsePropertiesMultilineWithoutTrailingSemicolon2() {
    $null = null;
    $collection =& parse_css_properties('font-weight: bold;
-html2ps-html-content: "Sample;Text"; 
color: red   

   ', $null);

    $properties = $collection->getPropertiesRaw();   
    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));
    $this->assertTrue($collection->contains(CSS_COLOR));
    $this->assertTrue($collection->contains(CSS_FONT_WEIGHT));
    
    $this->assertEqual($collection->getPropertyValue(CSS_FONT_WEIGHT), WEIGHT_BOLD);

    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "Sample;Text");

    $color = $collection->getPropertyValue(CSS_COLOR);
    $this->assertEqual($color->r, 1);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }

  function testCSSParsePropertiesMultilineWithLinefeedsAround() {
    $null = null;
    $collection =& parse_css_properties('
font-weight: bold;
-html2ps-html-content: "Sample;Text"; 
color: red;
', $null);

    $properties = $collection->getPropertiesRaw();   
    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));
    $this->assertTrue($collection->contains(CSS_COLOR));
    $this->assertTrue($collection->contains(CSS_FONT_WEIGHT));
    
    $this->assertEqual($collection->getPropertyValue(CSS_FONT_WEIGHT), WEIGHT_BOLD);

    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "Sample;Text");

    $color = $collection->getPropertyValue(CSS_COLOR);
    $this->assertEqual($color->r, 1);
    $this->assertEqual($color->g, 0);
    $this->assertEqual($color->b, 0);
  }
}

?>