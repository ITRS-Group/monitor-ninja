<?php

class TestWidows extends GenericTest {
  // No widows at all
  function testWidows1() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10pt; line-height: 1; orphans:0; widows: 0; padding: 0; margin: 0; }
#wrap  { width: 2em; }
#first { line-height: 1; height: 160pt; }
#second { width: 3em; }
</style>
</head>
<body>
<div id="first">&nbsp;</div>
<div id="second">
LINE1
LINE2
LINE3
LINE4<!--Page break should be here-->
LINE5
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

    $this->assertEqual(count($page_heights), 2,
                       sprintf("Two pages expected, got %s", 
                               count($page_heights)));

    $this->assertWithinMargin($page_heights[0], 
                              $first_div->get_full_height() + pt2pt(40),
                              0.01);
  }

  // Default widows value (2)
  function testWidows2() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
#first { line-height: 1; height: 160pt; }
#second { width: 3em; }
</style>
</head>
<body>
<div id="first">&nbsp;</div>
<div id="second">
LINE1
LINE2
LINE3<!--Page break should be here-->
LINE4
LINE5
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

    $this->assertEqual(count($page_heights), 2,
                       sprintf("Two pages expected, got %s", 
                               count($page_heights)));

    $this->assertWithinMargin($page_heights[0], 
                              $first_div->get_full_height() + pt2pt(30),
                              0.01);
  }

  // Increased widows value (3)
  function testWidows3() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; widows: 3; }
#first { line-height: 1; height: 160pt; }
#second { width: 3em; }
</style>
</head>
<body>
<div id="first">&nbsp;</div>
<div id="second">
LINE1
LINE2<!--Page break should be here-->
LINE3
LINE4
LINE5
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

    $this->assertEqual(count($page_heights), 2,
                       sprintf("Two pages expected, got %s", 
                               count($page_heights)));

    $this->assertWithinMargin($page_heights[0], 
                              $first_div->get_full_height() + pt2pt(20),
                              0.01);
  }
}

?>