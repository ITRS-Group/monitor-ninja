<?php

class TestPagebreakFixedHeight extends GenericTest {
  function testPagebreakFixedHeight1() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline(file_get_contents('test.pagebreak.fixed.height.1.html'), $media);

    $page_heights = PageBreakLocator::getPages($tree, 
                                               mm2pt($media->real_height()), 
                                               mm2pt($media->height() - $media->margins['top']));

    $div = $tree->get_element_by_id('div');

    $this->assertEqual(count($page_heights), 2,
                       sprintf("Two pages expected, got %s", 
                               count($page_heights)));
    $this->assertEqual($page_heights[0], 
                       200);
  }

}

?>