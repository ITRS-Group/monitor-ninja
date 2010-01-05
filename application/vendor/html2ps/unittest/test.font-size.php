<?php

class TestFontSize extends GenericTest {
  function testFontSizeMM() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body { font-size: 10mm; line-height: 1; }
</style>
</head>
<body>
TEXT
</body>
</html>
');

    $inline = $tree->get_first();
    $text = $inline->get_first();
    $this->assertEqual($text->words[0], "TEXT");
    $this->assertWithinMargin($text->get_full_height(), mm2pt(10), 0.01);
  }
}

?>