<?php

class TestIframeSrcEmpty extends GenericTest {
  function TestIframeSrcEmpty1() {
    $tree = $this->runPipeline('
<iframe src=""></iframe>
');

    // html2ps used  to loop on such IFRAMEs  indefinitely, dying with
    // "Frame nesting too deep" message
    $this->assertTrue(true);
  }
}

?>