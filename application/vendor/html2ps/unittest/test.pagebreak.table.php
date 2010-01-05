<?php

class TestPagebreakTable extends GenericTest {
  function testPagebreakTable1() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
</style>
</head>
<body>
<table cellpadding="0" cellspacing="0">
<tr><td>TEXT1</td><td>TEXT2</td></tr>
<tr><td>TEXT3</td><td>TEXT4</td></tr>
<tr><td>TEXT5</td><td>TEXT6</td></tr>
</table>
</body>
</html>
', $media);

    $locations = PageBreakLocator::_getBreakLocations($tree);
    $this->assertEqual(count($locations),
                       4);
  }

  function testPagebreakTable2() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
table  { line-height: 1; }
</style>
</head>
<body>
<table cellpadding="0" cellspacing="0" id="table">
<tr><td id="cell">TEXT1_1<br/>TEXT1_2</td></tr>
</table>
</body>
</html>
', $media);

    $locations = PageBreakLocator::_getBreakLocations($tree);

    $table = $tree->get_element_by_id('table');
    $cell  = $tree->get_element_by_id('cell');
    $line1 = $cell->content[0]->getLineBox(0);

    $this->assertEqual(count($locations),
                       3,
                       "Testing number of page breaks inside a table with one cell & several text lines inside [%s]");
    $this->assertEqual($locations[0]->location,
                       $table->get_top_margin(),
                       "First page break should be at the table top [%s]");
    $this->assertEqual($locations[1]->location,
                       $line1->bottom,
                       "Second page break should be at the bottom of the first line box in the table cell [%s]");
    $this->assertEqual($locations[2]->location,
                       $table->get_bottom_margin(),
                       "Last page break should be at the table bottom [%s]");
  }
}

?>