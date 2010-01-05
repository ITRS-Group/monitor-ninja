<?php

class TestIframeSrcMissing extends GenericTest {
  function TestIframeSrcMissing1() {
    $tree = $this->runPipeline('
<iframe></iframe>
');

    // some  html2ps versions  treated such  contruct  as indefinitely
    // nesting frames
    $this->assertTrue(true);
  }
}

?>