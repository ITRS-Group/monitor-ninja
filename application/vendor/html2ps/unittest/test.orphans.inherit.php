<?php

class TestOrphansInherit extends GenericTest {
  function testOrphansInherit1() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10mm; line-height: 1; orphans:0; widows: 0; padding: 0; margin: 0; }
#first { line-height: 1; height: 190mm; }
</style>
</head>
<body>
<div id="first">&nbsp;</div>
<div id="second">
LINE1<!--Page break should be here-->
LINE2
LINE3
LINE4
LINE5
</div>
</body>
</html>
');

    $div = $tree->get_element_by_id('first');
    $this->assertEqual($div->getCSSProperty(CSS_ORPHANS), 0);

    $div = $tree->get_element_by_id('second');
    $this->assertEqual($div->getCSSProperty(CSS_ORPHANS), 0);
  }
}

?>