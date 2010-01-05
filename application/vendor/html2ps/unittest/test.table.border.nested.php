<?php

class TestTableBorderNested extends GenericTest {
  function TestTableBorderNested1() {
    $tree = $this->runPipeline('
<table border="1" cellspacing="1" cellpadding="2">
<tr>
<td>
<table id="table">
<tr>
<td id="cell">TEXT</td>
</tr>
</table>
</td>
</tr>
</table>
');

    $table = $tree->get_element_by_id('table');
    $table_border = $table->getCSSProperty(CSS_BORDER);
    $this->assertEqual($table_border->left->style , BS_NONE);
    $this->assertEqual($table_border->right->style , BS_NONE);
    $this->assertEqual($table_border->top->style , BS_NONE);
    $this->assertEqual($table_border->bottom->style , BS_NONE);

    $cell = $tree->get_element_by_id('cell');
    $cell_border = $cell->getCSSProperty(CSS_BORDER);
    $this->assertEqual($cell_border->left->style , BS_NONE);
    $this->assertEqual($cell_border->right->style , BS_NONE);
    $this->assertEqual($cell_border->top->style , BS_NONE);
    $this->assertEqual($cell_border->bottom->style , BS_NONE);
  }
}

?>