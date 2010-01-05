<?php

class TestLeftPercentage extends GenericTest {
  function TestLeftPercentage1() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
#wrapper { width: 100mm; position: relative; }
#div0 { position: absolute; top: 0; left:   0%; }
#div1 { position: absolute; top: 0; left:  20%; }
#div2 { position: absolute; top: 0; left:  40%; }
#div3 { position: absolute; top: 0; left:  60%; }
#div4 { position: absolute; top: 0; left:  80%; }
#div5 { position: absolute; top: 0; left: 100%; }
</style>
</head>
<body>
<div id="wrapper">
<div id="div0">DIV1</div>
<div id="div1">DIV1</div>
<div id="div2">DIV1</div>
<div id="div3">DIV1</div>
<div id="div4">DIV1</div>
<div id="div5">DIV1</div>
</div>
</body>
</html>
');

    
    $wrapper = $tree->get_element_by_id('wrapper');
    for ($i=0; $i<=5; $i++) {
      $div = $tree->get_element_by_id(sprintf('div%d', $i));
      $this->assertEqual($wrapper->get_left() + $wrapper->get_width() / 5 * $i,
                         $div->get_left());
    };
  }

  function TestLeftPercentage2() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
#wrapper { width: 100mm; position: relative; }
.offset {
  position: absolute;
  top: 0px;
  left: 0px;
}
</style>
</head>
<body>
<div id="wrapper">
<div id="div0" class="offset" style="left:   0%">DIV1</div>
<div id="div1" class="offset" style="left:  20%">DIV1</div>
<div id="div2" class="offset" style="left:  40%">DIV1</div>
<div id="div3" class="offset" style="left:  60%">DIV1</div>
<div id="div4" class="offset" style="left:  80%">DIV1</div>
<div id="div5" class="offset" style="left: 100%">DIV1</div>
</div>
</body>
</html>
');

    
    $wrapper = $tree->get_element_by_id('wrapper');
    for ($i=0; $i<=5; $i++) {
      $div_id = sprintf('div%d', $i);
      $div = $tree->get_element_by_id($div_id);
      $this->assertEqual($wrapper->get_left() + $wrapper->get_width() / 5 * $i,
                         $div->get_left());
    };
  }

  function TestLeftPercentage3() {
    $tree = $this->runPipeline('
<html><head>
<style type="text/css">
<!--
.timeContainer {
  position: relative;
  top: 0px;
  left: 0px;
  width: 80%;
}

.time {
	position: absolute;
	top: 0px;
	left: 0px;
}
-->
</style>
</head>
<body>
<div id="wrapper" class="timecontainer">
<div id="div0" class="time" style="left: 0%">08:00</div>
<div id="div1" class="time" style="left: 20%">10:00</div>
<div id="div2" class="time" style="left: 40%">12:00</div>
<div id="div3" class="time" style="left: 60%">14:00</div>
<div id="div4" class="time" style="left: 80%">16:00</div>
<div id="div5" class="time" style="left: 100%">18:00</div>
</div>
</body></html>
', $media, $pipeline, $context, $postponed);

    $wrapper = $tree->get_element_by_id('wrapper');
    for ($i=0; $i<=5; $i++) {
      $div_id = sprintf('div%d', $i);
      $div = $tree->get_element_by_id($div_id);
      $this->assertEqual($wrapper->get_left() + $wrapper->get_width() / 5 * $i,
                         $div->get_left());
    };
  }
}

?>