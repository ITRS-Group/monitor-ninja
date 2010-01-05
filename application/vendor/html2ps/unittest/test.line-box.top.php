<?php

class TestLineBoxTop extends GenericTest {
  function testLineBoxTop1() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
</style>
</head>
<body>TEXT</body>
</html>
', $media);

    $line_box = $tree->content[0]->getLineBox(0);

    $this->assertEqual($tree->content[0]->get_top(),
                       $line_box->top,
                       "Comparing line box top and inline box top [%s]");
  }

}

?>