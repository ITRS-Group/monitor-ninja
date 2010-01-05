<?php

class TestCSSPriority extends GenericTest {
  function testCSSPriority1() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
#cell1 { background-color: red; }
#cell2 { background-color: lime; }
#cell3 { }
</style>
</head>
<body>
<table>
<tr>
<td bgcolor="green" id="cell1">&nbsp;</td>
<td id="cell2">&nbsp;</td>
<td bgcolor="blue" id="cell3">&nbsp;</td>
</tr>
</table>
</body>
</html>
');

    $cell1 =& $tree->get_element_by_id('cell1');
    $color =& $cell1->getCSSProperty(CSS_BACKGROUND_COLOR);
    $this->assertEqual(1, $color->r);
    $this->assertEqual(0, $color->g);
    $this->assertEqual(0, $color->b);

    $cell2 =& $tree->get_element_by_id('cell2');
    $color =& $cell2->getCSSProperty(CSS_BACKGROUND_COLOR);
    $this->assertEqual(0, $color->r);
    $this->assertEqual(1, $color->g);
    $this->assertEqual(0, $color->b);

    $cell3 =& $tree->get_element_by_id('cell3');
    $color =& $cell3->getCSSProperty(CSS_BACKGROUND_COLOR);
    $this->assertEqual(0, $color->r);
    $this->assertEqual(0, $color->g);
    $this->assertEqual(1, $color->b);
  }
}

?>