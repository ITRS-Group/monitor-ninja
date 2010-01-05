<?php

class TestTableTopBoundary extends GenericTest {
  function testTableTopBoundary1() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
</style>
</head>
<body>
<table cellpadding="0" cellspacing="0" id="table">
<tr><td id="cell">TEXT1</td></tr>
</table>
</body>
</html>
', $media);

    $table = $tree->get_element_by_id('table');
    $cell  = $tree->get_element_by_id('cell');

    $this->assertEqual($table->get_top_margin(),
                       $cell->get_top_margin(),
                       "Comparing table and cell top margins for the table containins one cell [%s]");
    $text = $cell->content[0]->content[0];
    $this->assertEqual($text->get_top_margin(),
                       $cell->get_top_margin(),
                       "Comparing cell and cell content top margins for the table containins one cell [%s]");
  }

  function testTableTopBoundary2() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
</style>
</head>
<body>
<table cellpadding="0" cellspacing="0" id="table">
<tr><td id="cell">TEXT1<br/>TEXT2</td></tr>
</table>
</body>
</html>
', $media);

    $table = $tree->get_element_by_id('table');
    $cell  = $tree->get_element_by_id('cell');

    $this->assertEqual($table->get_top_margin(),
                       $cell->get_top_margin(),
                       "Comparing table and cell top margins for the table containins one cell [%s]");
    $text = $cell->content[0]->content[0];
    $this->assertEqual($text->get_top_margin(),
                       $cell->get_top_margin(),
                       "Comparing cell and cell content top margins for the table containins one cell [%s]");
  }
}

?>