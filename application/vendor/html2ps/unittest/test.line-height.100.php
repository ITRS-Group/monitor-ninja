<?php

class TestLineHeight100 extends GenericTest {
  function testHeightsWithBR() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10mm; }
#div1 { height: 100mm; float: left; background-color: green; width: 100mm; }
#div2 { line-height: 1; float: left; background-color: red; width: 100mm; }
</style>
</head>
<body>
<div id="div1">&nbsp;</div>
<div id="div2">
LINE1<br/>
LINE2<br/>
LINE3<br/>
LINE4<br/>
LINE5<br/>
LINE6<br/>
LINE7<br/>
LINE8<br/>
LINE9<br/>
LINE10<br/>
</div>
</body>
</html>
');

    $first_div  = $tree->get_element_by_id('div1');
    $second_div = $tree->get_element_by_id('div2');

    $this->assertWithinMargin($first_div->get_full_height(), 
                              $second_div->get_full_height(),
                              0.01,
                              "DIVs have different heights! [%s]");
  }

  function testHeightsWithoutBR() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body   { font-size: 10mm; }
#div1 { height: 100mm; float: left; background-color: green; width: 100mm; }
#div2 { line-height: 1; float: left; background-color: red; width: 2em; }
</style>
</head>
<body>
<div id="div1">&nbsp;</div>
<div id="div2">
LINE1
LINE2
LINE3
LINE4
LINE5
LINE6
LINE7
LINE8
LINE9
LINE10
</div>
</body>
</html>
');

    $first_div  = $tree->get_element_by_id('div1');
    $second_div = $tree->get_element_by_id('div2');

    $this->assertWithinMargin($first_div->get_full_height(), 
                              $second_div->get_full_height(),
                              0.01,
                              "DIVs have different heights! [%s]");
  }
}

?>