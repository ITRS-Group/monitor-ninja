<?php

class TestHtmlMode extends GenericTest {
  function testHtmlMode1() {
    $tree = $this->runPipeline(file_get_contents('test.html.mode.1.html'));

    $this->assertEqual($GLOBALS['g_config']['mode'], 'quirks');
  }
}

?>