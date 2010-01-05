<?php

class TestFontInherit extends GenericTest {
  function testInheritEM() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10pt; }
#outer { font: 2em sans-serif; }
#inner { font: inherit; }
</style>
</head>
<body>
<div id="outer" class="outer">
NORMAL
<div id="inner">inner</div>
NORMAL
</div>
</body>
</html>
');

    $outer_div = $tree->get_element_by_id('outer');
    $inner_div = $tree->get_element_by_id('inner');

    $body_font_size  = $tree->getCSSProperty(CSS_FONT_SIZE);
    $outer_font_size = $outer_div->getCSSProperty(CSS_FONT_SIZE);
    $inner_font_size = $inner_div->getCSSProperty(CSS_FONT_SIZE);
 
    $this->assertEqual($body_font_size->getPoints()*2, 
                       $outer_font_size->getPoints());
    $this->assertEqual($outer_font_size->getPoints(), 
                       $inner_font_size->getPoints());
  }

  function testEMinEM() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10pt; }
#outer { font: 2em sans-serif; }
#inner { font: 2em; }
</style>
</head>
<body>
<div id="outer" class="outer">
NORMAL
<div id="inner">inner</div>
NORMAL
</div>
</body>
</html>
');
    $outer_div = $tree->get_element_by_id('outer');
    $inner_div = $tree->get_element_by_id('inner');

    $body_font_size  = $tree->getCSSProperty(CSS_FONT_SIZE);
    $outer_font_size = $outer_div->getCSSProperty(CSS_FONT_SIZE);
    $inner_font_size = $inner_div->getCSSProperty(CSS_FONT_SIZE);

    $this->assertEqual($body_font_size->getPoints()*2, 
                       $outer_font_size->getPoints());
    $this->assertEqual($outer_font_size->getPoints()*2,
                       $inner_font_size->getPoints());
  }
}

?>