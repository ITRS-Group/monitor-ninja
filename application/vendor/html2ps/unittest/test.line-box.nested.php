<?php

class TestLineBoxNested extends GenericTest {
  function testLineBoxNested1() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
</style>
</head>
<body>
<span id="outer" style="border: solid black 1px;">
<span id="inner1" style="background-color: red;">TEXT</span>
<span id="inner2" style="background-color: green; font-size: 2em;">TEXT</span>
<span id="inner3" style="background-color: red;">TEXT</span>
</span>
</body>
</html>
', $media);

    $outer = $tree->get_element_by_id('outer');
    $outer_line = $outer->getLineBox(0);

    $inner1 = $tree->get_element_by_id('inner1');
    $inner1_line = $inner1->getLineBox(0);

    $inner2 = $tree->get_element_by_id('inner2');
    $inner2_line = $inner2->getLineBox(0);

    $inner3 = $tree->get_element_by_id('inner3');
    $inner3_line = $inner3->getLineBox(0);

    // Note that it will emulate IE behavior (line box includes all 
    // nested line boxes), which (in my opinion)
    // is more standard than Firefox (line box height is calculated 
    // using the first child line box).
    
    $this->assertEqual($outer_line->top, $inner2_line->top);
    $this->assertEqual($outer_line->bottom, $inner2_line->bottom);
    $this->assertEqual($inner1_line->bottom, $inner3_line->bottom);
    $this->assertEqual($inner1_line->top, $inner3_line->top);
    $this->assertTrue($inner1_line->top < $inner2_line->top);
    $this->assertTrue($inner1_line->bottom > $inner2_line->bottom);
  }
}

?>