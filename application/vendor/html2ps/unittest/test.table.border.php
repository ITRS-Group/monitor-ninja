<?php

class TestTableBorder extends GenericTest {
  function TestTableBorder1() {
    $tree = $this->runPipeline('
<html><head>
</head>
<body>
<table id="table" border="1" cellspacing="1" cellpadding="2">
<tr>
<td id="cell1">TEXT</td>
<td id="cell2">TEXT</td>
</tr>
</table>
</body>
</html>
');

    $table = $tree->get_element_by_id('table');
    $table_border = $table->getCSSProperty(CSS_BORDER);

    $this->assertEqual($table_border->left->style , BS_SOLID);
    $this->assertEqual($table_border->right->style , BS_SOLID);
    $this->assertEqual($table_border->top->style , BS_SOLID);
    $this->assertEqual($table_border->bottom->style , BS_SOLID);

    $this->assertTrue($table_border->left->width->getPoints() > 0);
    $this->assertTrue($table_border->right->width->getPoints() > 0);
    $this->assertTrue($table_border->top->width->getPoints() > 0);
    $this->assertTrue($table_border->bottom->width->getPoints() > 0);

    $cell1 = $tree->get_element_by_id('cell1');
    $cell1_border = $cell1->getCSSProperty(CSS_BORDER);
    $this->assertEqual($cell1_border->left->style , BS_SOLID);
    $this->assertEqual($cell1_border->right->style , BS_SOLID);
    $this->assertEqual($cell1_border->top->style , BS_SOLID);
    $this->assertEqual($cell1_border->bottom->style , BS_SOLID);

    $this->assertTrue($cell1_border->left->width->getPoints() > 0,
                      'Expected non-zero left border width');
    $this->assertTrue($cell1_border->right->width->getPoints() > 0,
                      'Expected non-zero right border width');
    $this->assertTrue($cell1_border->top->width->getPoints() > 0,
                      'Expected non-zero top border width');
    $this->assertTrue($cell1_border->bottom->width->getPoints() > 0,
                      'Expected non-zero bottom border width');

    $cell2 = $tree->get_element_by_id('cell2');
    $cell2_border = $cell2->getCSSProperty(CSS_BORDER);
    $this->assertEqual($cell2_border->left->style , BS_SOLID);
    $this->assertEqual($cell2_border->right->style , BS_SOLID);
    $this->assertEqual($cell2_border->top->style , BS_SOLID);
    $this->assertEqual($cell2_border->bottom->style , BS_SOLID);

    $this->assertTrue($cell2_border->left->width->getPoints() > 0,
                      'Expected non-zero left border width');
    $this->assertTrue($cell2_border->right->width->getPoints() > 0,
                      'Expected non-zero right border width');
    $this->assertTrue($cell2_border->top->width->getPoints() > 0,
                      'Expected non-zero top border width');
    $this->assertTrue($cell2_border->bottom->width->getPoints() > 0,
                      'Expected non-zero bottom border width');
  }

  function TestTableBorder2() {
    $tree = $this->runPipeline('
<html><head>
</head>
<body>
<table id="table" border="1" cellspacing="1" cellpadding="2">
<tbody>
<tr>
<td id="cell1">TEXT</td>
<td id="cell2">TEXT</td>
</tr>
</tbody>
</table>
</body>
</html>
');

    $table = $tree->get_element_by_id('table');
    $table_border = $table->getCSSProperty(CSS_BORDER);

    $this->assertEqual($table_border->left->style , BS_SOLID);
    $this->assertEqual($table_border->right->style , BS_SOLID);
    $this->assertEqual($table_border->top->style , BS_SOLID);
    $this->assertEqual($table_border->bottom->style , BS_SOLID);

    $this->assertTrue($table_border->left->width->getPoints() > 0);
    $this->assertTrue($table_border->right->width->getPoints() > 0);
    $this->assertTrue($table_border->top->width->getPoints() > 0);
    $this->assertTrue($table_border->bottom->width->getPoints() > 0);

    $cell1 = $tree->get_element_by_id('cell1');
    $cell1_border = $cell1->getCSSProperty(CSS_BORDER);
    $this->assertEqual($cell1_border->left->style , BS_SOLID);
    $this->assertEqual($cell1_border->right->style , BS_SOLID);
    $this->assertEqual($cell1_border->top->style , BS_SOLID);
    $this->assertEqual($cell1_border->bottom->style , BS_SOLID);

    $this->assertTrue($cell1_border->left->width->getPoints() > 0,
                      'Expected non-zero left border width');
    $this->assertTrue($cell1_border->right->width->getPoints() > 0,
                      'Expected non-zero right border width');
    $this->assertTrue($cell1_border->top->width->getPoints() > 0,
                      'Expected non-zero top border width');
    $this->assertTrue($cell1_border->bottom->width->getPoints() > 0,
                      'Expected non-zero bottom border width');

    $cell2 = $tree->get_element_by_id('cell2');
    $cell2_border = $cell2->getCSSProperty(CSS_BORDER);
    $this->assertEqual($cell2_border->left->style , BS_SOLID);
    $this->assertEqual($cell2_border->right->style , BS_SOLID);
    $this->assertEqual($cell2_border->top->style , BS_SOLID);
    $this->assertEqual($cell2_border->bottom->style , BS_SOLID);

    $this->assertTrue($cell2_border->left->width->getPoints() > 0,
                      'Expected non-zero left border width');
    $this->assertTrue($cell2_border->right->width->getPoints() > 0,
                      'Expected non-zero right border width');
    $this->assertTrue($cell2_border->top->width->getPoints() > 0,
                      'Expected non-zero top border width');
    $this->assertTrue($cell2_border->bottom->width->getPoints() > 0,
                      'Expected non-zero bottom border width');
  }
}

?>