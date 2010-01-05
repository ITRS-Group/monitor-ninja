<?php

class TestFloat extends GenericTest {
  function testFloatLeft() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10mm; }
#div1 { float: left; }
</style>
</head>
<body>
<div id="div1">&nbsp;</div>
</body>
</html>
');

    $first_div  = $tree->get_element_by_id('div1');
    $body       = $tree;

    $this->assertEqual($body->get_left(),
                       $first_div->get_left_margin());
  }

  function testFloatRight() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10mm; }
#div1 { float: right; }
</style>
</head>
<body>
<div id="div1">&nbsp;</div>
</body>
</html>
');

    $first_div  = $tree->get_element_by_id('div1');
    $body       = $tree;

    $this->assertEqual($body->get_right(),
                       $first_div->get_right_margin());
  }
}

?>