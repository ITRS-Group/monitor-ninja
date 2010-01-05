<?php

class TestPagebreakTableLines extends GenericTest {
  /**
   * Checks if it is possible to make an incorrect page break in a table with
   * different font size in different cells
   */
  function testPagebreakTableLines1() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body { 
  font-size: 10pt; 
  line-height: 1; 
  padding: 0; 
  margin: 0; 
  width: 10pt;
}

div { 
  width: 10pt;
  orphans: 0;
  widows: 0;
}

td#small { 
  font-size: 20pt; 
  line-height: 1; 
  width: 10pt;
  vertical-align: top;
}

td#large { 
  font-size: 30pt; 
  line-height: 1; 
  width: 10pt;
  vertical-align: top;
}
</style>
</head>
<body>
<table cellpadding="0" cellspacing="0">
<tr>
<td id="small">
<div>
SMALL
SMALL
SMALL
SMALL
SMALL
SMALL
SMALL
SMALL
SMALL
SMALL
</div>
</td>
<td id="large">
<div>
LARGE
LARGE
LARGE
LARGE
LARGE
LARGE
LARGE
</div>
</td>
</tr>
</table>
</body>
</html>
', $media);


    $small = $tree->get_element_by_id('small');
    $font_size =& $small->getCSSProperty(CSS_FONT_SIZE);
    $this->assertEqual($font_size->getPoints(), 20);

    $large = $tree->get_element_by_id('large');
    $font_size =& $large->getCSSProperty(CSS_FONT_SIZE);
    $this->assertEqual($font_size->getPoints(), 30);
  
    $locations = PageBreakLocator::_getBreakLocations($tree);
    $this->assertEqual(count($locations), 5);

    $page_heights = PageBreakLocator::getPages($tree, 
                                               mm2pt($media->real_height()), 
                                               mm2pt($media->height() - $media->margins['top']));

    $this->assertEqual(count($page_heights), 2,
                       sprintf("Two pages expected, got %s", 
                               count($page_heights)));
    $this->assertEqual($page_heights[0], 
                       pt2pt(180));
  }
}

?>