<?php

class TestPagebreakTableBr extends GenericTest {
  /**
   * Checks if it is possible to make an incorrect page break in a table with
   * different font size in different cells
   */
  function testPagebreakTableBr1() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body { 
  font-size: 10pt; 
  line-height: 1; 
  padding: 0; 
  margin: 0; 
}

td.small { 
  font-size: 20pt; 
}
</style>
</head>
<body>
<table cellpadding="0" cellspacing="0">
<tr>
<td>
SMALL<br/>
SMALL<br/>
SMALL<br/>
SMALL<br/>
SMALL<br/>
</td>
</tr>
</table>
</body>
</html>
', $media);

    /**
     * Calculate page heights
     */
    $locations = PageBreakLocator::_getBreakLocations($tree);
    $this->assertEqual(count($locations), 6);
  }
}

?>