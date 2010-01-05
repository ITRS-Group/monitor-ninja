<?php

class TestRelative extends GenericTest {
  function testRelativeNoOffset() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10mm; }
#div1 { position: relative; }
</style>
</head>
<body>
<div id="div1">&nbsp;</div>
</body>
</html>
');

    $body       = $tree;
    $first_div  = $tree->get_element_by_id('div1');

    $this->assertEqual($body->get_left(),
                      $first_div->get_left_margin());
    $this->assertEqual($body->get_top(),
                      $first_div->get_top_margin());
  }
}

?>