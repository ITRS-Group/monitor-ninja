<?php

class TestBlockAbsolute extends GenericTest {
  function TestBlockAbsolute1() {
    $tree = $this->runPipeline('
<style type="text/css">
body {
  margin: 0;
  padding: 0;
}
</style>
<div id="block" style="position: absolute; left: 10mm; top: 20mm;">
&nbsp;
</div>
');

    $block = $tree->get_element_by_id('block');
    $body = $tree->get_body();

    $this->assertEqual($block->get_top_margin(), $body->get_top() - mm2pt(20));
    $this->assertEqual($block->get_left_margin(), mm2pt(10));
  }
}

?>