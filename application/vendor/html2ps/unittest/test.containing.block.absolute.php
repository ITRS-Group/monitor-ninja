<?php

class TestContainingBlockAbsolute extends GenericTest {
  function TestContainingBlockAbsolute1() {
    $tree = $this->runPipeline('
<style type="text/css">
#container {
  padding: 10mm;
  margin: 50mm;
  position: relative;
  top: 0;
  left: 0;
}

#block {
  position: absolute;
  top: 3mm;
  left: 2mm;
  margin: 7mm;
}
</style>
<div id="container">
<div id="block">
&nbsp;
</div><!--#block-->
</div><!--#container-->
');

    $block     =& $tree->get_element_by_id('block');
    $container =& $tree->get_element_by_id('container');

    $this->assertEqual($block->get_top_margin(), $container->get_top_padding() - mm2pt(3));
    $this->assertEqual($block->get_left_margin(), $container->get_left_padding() + mm2pt(2));
  }
}

?>