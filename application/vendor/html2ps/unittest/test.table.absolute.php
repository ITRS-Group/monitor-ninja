<?php

class TestTableAbsolute extends GenericTest {
  function TestTableAbsolute1() {
    $tree = $this->runPipeline('
<style type="text/css">
body {
  margin: 0;
  padding: 0;
}
</style>
<table id="table" style="position: absolute; left: 11mm; top: 17mm;" cellpadding="0" cellspacing="0">
<tr><td>&nbsp;</td></tr>
</table>
');

    $table = $tree->get_element_by_id('table');
    $body = $tree->get_body();

    $this->assertEqual($table->get_top_margin(), $body->get_top() - mm2pt(17));
    $this->assertEqual($table->get_left_margin(), mm2pt(11));
  }
}

?>