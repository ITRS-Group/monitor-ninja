<?php

class TestCSSPageBreakInside extends GenericTest {
  function testCSSPageBreakInside1() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
#div { page-break-inside: avoid; }
</style>
</head>
<body>
<div id="div">&nbsp;</div>
</body>
</html>
');

    $div = $tree->get_element_by_id('div');

    $this->assertEqual(PAGE_BREAK_AVOID, $div->getCSSProperty(CSS_PAGE_BREAK_INSIDE));
  }
}

?>