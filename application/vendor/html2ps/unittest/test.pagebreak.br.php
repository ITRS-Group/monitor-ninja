<?php

class TestPagebreakBr extends GenericTest {
  function testPagebreakNormal() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
</style>
</head>
<body>
<div style="width: 1em;">
LINE1
LINE2
LINE3
LINE4
LINE5
</div>
</body>
</html>
', $media);

    $locations = PageBreakLocator::_getBreakLocations($tree);
    $this->assertEqual(count($locations),
                       6);
  }

  function testPagebreakWithBR() {
    $media = new Media(array('width' => 100, 'height' => 200/mm2pt(1)),
                       array('top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0));
    $tree = $this->runPipeline('
<html>
<head>
body   { font-size: 10pt; line-height: 1; padding: 0; margin: 0; }
</style>
</head>
<body>
<div>
LINE1<br/>
LINE2<br/>
LINE3<br/>
LINE4<br/>
LINE5<br/>
</div>
</body>
</html>
', $media);

    $locations = PageBreakLocator::_getBreakLocations($tree);
    $this->assertEqual(count($locations),
                       6);
  }
}

?>