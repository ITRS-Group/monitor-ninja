<?php

class TestTableColumnWidth extends GenericTest {
  function testTableColumnWidth1() {
    $media = new Media(array('width' => 100, 
                             'height' => 200/mm2pt(1)),
                       array('top'=>0, 
                             'bottom'=>0, 
                             'left'=>0, 
                             'right'=>0));
    $tree = $this->runPipeline(file_get_contents('test.table.column.width.1.html'), 
                               $media,
                               $pipeline);
    $large = $tree->get_element_by_id('large');

    $real_width = max($pipeline->output_driver->stringwidth('LARGE', 'Times-Roman', 'iso-8859-1', pt2pt(30)),
                      pt2pt(10));

    $width =& $large->getCSSProperty(CSS_WIDTH);
    $this->assertTrue($width->isConstant());
    $this->assertEqual($width->width, $real_width);
    $this->assertEqual($large->get_width(), $real_width);
  }

  function testTableColumnWidth2() {
    $media = new Media(array('width' => 100, 
                             'height' => 200/mm2pt(1)),
                       array('top'=>0, 
                             'bottom'=>0, 
                             'left'=>0, 
                             'right'=>0));
    $tree = $this->runPipeline(file_get_contents('test.table.column.width.2.html'), 
                               $media,
                               $pipeline);
    $large = $tree->get_element_by_id('large');

    $real_width = pt2pt(150);

    $width =& $large->getCSSProperty(CSS_WIDTH);
    $this->assertTrue($width->isConstant());
    $this->assertEqual($width->width, $real_width);
    $this->assertEqual($large->get_width(), $real_width);
  }

  function testTableColumnWidth3() {
    $media = null;
    $tree = $this->runPipeline(file_get_contents('test.table.column.width.3.html'), $media);

    $container_table =& $tree->get_element_by_id('table');
    $cell =& $tree->get_element_by_id('container-cell');
    $table =& $tree->get_element_by_id('contained-table');

    $this->assertEqual($container_table->get_width(), mm2pt($media->real_width()));
    $this->assertTrue($cell->get_width() >= $table->get_width(),
                      sprintf("Cell width (%s) is less than content table width (%s)", 
                              $cell->get_width(),
                              $table->get_width()));
  }

  function testTableColumnWidth4() {
    $media = null;
    $tree = $this->runPipeline(file_get_contents('test.table.column.width.4.html'), $media);

    $container_table =& $tree->get_element_by_id('table');
    $cell1 =& $tree->get_element_by_id('cell1');
    $cell2 =& $tree->get_element_by_id('cell2');
    $cell =& $tree->get_element_by_id('container-cell');

    $this->assertEqual($container_table->get_width(), mm2pt($media->real_width()) * 0.9);

    $container_cell_width = $cell->get_width();
    $container_cell_min_width = $cell->content[0]->get_width();
    $this->assertTrue($container_cell_min_width <= $container_cell_width,
                      sprintf('Container cell width (%s) is less than content minimal width (%s)',
                              $container_cell_width, 
                              $container_cell_min_width));

    $cell_width = $cell1->get_width() + $cell2->get_width() + $cell->get_width();
    $table_width = $container_table->get_width();
    $this->assertTrue($cell_width <=
                      $table_width,
                      sprintf('Total cell width (%s) is greater than table width (%s)',
                              $cell_width, 
                              $table_width));
  }
}

?>