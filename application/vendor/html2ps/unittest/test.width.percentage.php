<?php

class TestWidthPercentage extends GenericTest {
  function TestWidthPercentage1() {
    $tree = $this->runPipeline('
<html><head>
<style type="text/css">
<!--
.timeContainer {
  position: relative;
  width: 80%;
}
-->
</style>
</head>
<body>
<div id="wrapper" class="timecontainer">X</div>
</body>
</html>
', $media, $pipeline, $context, $postponed);

    $wrapper = $tree->get_element_by_id('wrapper');

    $this->assertTrue($wrapper->get_width() > 0,
                      sprintf('Non-zero width expected, got %s', $wrapper->get_width()));
  }
}

?>