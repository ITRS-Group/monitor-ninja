<?php

class TestPagebreak extends GenericTest {
  // Page break between text lines
  function testPagebreakText1() {
    $media = new Media(array('width' => 100, 'height' => 100),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline(file_get_contents('test.pagebreak.text.1.html'), $media);

    /**
     * Calculate page heights
     */
    $page_heights = PageBreakLocator::getPages($tree, 
                                               mm2pt($media->real_height()), 
                                               mm2pt($media->height() - $media->margins['top']));

    $first_div  = $tree->get_element_by_id('first');
    $second_div = $tree->get_element_by_id('second');

    $this->assertEqual(count($page_heights), 2,
                       sprintf("Two pages expected, got %s", 
                               count($page_heights)));

    $this->assertTrue($page_heights[0] <= mm2pt($media->real_height()),
                      sprintf("Page height (%s) is greater than media height (%s)", 
                              $page_heights[0], 
                              mm2pt($media->real_height())));

    $this->assertEqual($page_heights[1], 
                       mm2pt($media->real_height()),
                       sprintf("Second page height (%s) should be equal to media height (%s)", 
                               $page_heights[1], 
                               mm2pt($media->real_height())));

    $this->assertWithinMargin($page_heights[0], 
                              $first_div->get_full_height(),
                              0.01,
                              sprintf("First page (height %s) should contain 'first' DIV (height %s); media height is %s",
                                      $page_heights[0],
                                      $first_div->get_full_height(),
                                      mm2pt($media->real_height())));

    $this->assertEqual($page_heights[1] >= $second_div->get_full_height(),
                       sprintf("Second page (height %s) should contain 'second' DIV (height %s)",
                               $page_heights[1], 
                               $second_div->get_full_height()));
  }

  // page-break-inside: avoid
  function testPagebreakText2() {
    $media = new Media(array('width' => 100, 'height' => 300),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline(file_get_contents('test.pagebreak.text.2.html'), $media);

    /**
     * Calculate page heights
     */
    $page_heights = PageBreakLocator::getPages($tree, 
                                               mm2pt($media->real_height()), 
                                               mm2pt($media->height() - $media->margins['top']));

    $first_div  = $tree->get_element_by_id('first');
    $second_div = $tree->get_element_by_id('second');
    $third_div  = $tree->get_element_by_id('third');

    $this->assertEqual(count($page_heights), 2,
                       sprintf("2 pages expected, got %s", 
                               count($page_heights)));

    $this->assertWithinMargin($page_heights[0], 
                              $first_div->get_full_height(),
                              0.01,
                              sprintf("First page (height %s) should contain only 'first' DIV (height %s); media height is %s",
                                      $page_heights[0],
                                      $first_div->get_full_height(),
                                      mm2pt($media->real_height())));
  }

  // page-break-after: avoid
  function testPagebreakText3() {
    $media = new Media(array('width' => 100, 'height' => 300),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body    { font-size: 20mm; line-height: 1; width: 4em;  padding: 0; margin: 0; orphans: 0; widows: 0; }
#wrap   { width: 2em; }
#first  { line-height: 1; page-break-after: avoid; }
#second { line-height: 1; page-break-inside: avoid; }
#third  { line-height: 1; }
</style>
</head>
<body>
<div id="wrap">
<div id="first">
LINE1
LINE2
LINE3
LINE4
LINE5
LINE6
LINE7
LINE8
LINE9
LINE10
LINE11
LINE12
LINE13
</div><!--Page break should be here-->
<div id="second">
LINE1
LINE2
LINE3
</div>
<div id="third">
LINE1
LINE2
LINE3
</div>
</div>
</body>
</html>
', $media);

    /**
     * Calculate page heights
     */
    $page_heights = PageBreakLocator::getPages($tree, 
                                               mm2pt($media->real_height()), 
                                               mm2pt($media->height() - $media->margins['top']));

    $first_div  = $tree->get_element_by_id('first');
    $second_div = $tree->get_element_by_id('second');
    $third_div  = $tree->get_element_by_id('third');

    $this->assertEqual(count($page_heights), 2,
                       sprintf("2 pages expected, got %s", 
                               count($page_heights)));

    $this->assertWithinMargin($page_heights[0], 
                              $first_div->get_full_height() - mm2pt(20),
                              0.01);
  }

  // page-break-before: avoid
  function testPagebreakText4() {
    $media = new Media(array('width' => 100, 'height' => 300),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body    { font-size: 20mm; line-height: 1; padding: 0; margin: 0; orphans: 0; widows: 0; }
#wrap   { width: 2em; }
#first  { line-height: 1; }
#second { line-height: 1; page-break-before: avoid; page-break-inside: avoid; }
#third  { line-height: 1; }
</style>
</head>
<body>
<div id="wrap">
<div id="first">
LINE1
LINE2
LINE3
LINE4
LINE5
LINE6
LINE7
LINE8
LINE9
LINE10
LINE11
LINE12
LINE13
</div><!--Page break should be here-->
<div id="second">
LINE1
LINE2
LINE3
</div>
<div id="third">
LINE1
LINE2
LINE3
</div>
</div>
</body>
</html>
', $media);

    /**
     * Calculate page heights
     */
    $page_heights = PageBreakLocator::getPages($tree, 
                                               mm2pt($media->real_height()), 
                                               mm2pt($media->height() - $media->margins['top']));

    $first_div  = $tree->get_element_by_id('first');
    $second_div = $tree->get_element_by_id('second');
    $third_div  = $tree->get_element_by_id('third');

    $this->assertEqual(count($page_heights), 2,
                       sprintf("2 pages expected, got %s", 
                               count($page_heights)));

    $this->assertWithinMargin($page_heights[0], 
                              $first_div->get_full_height() - mm2pt(20),
                              0.01);
  }
}

?>