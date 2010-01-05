<?php

class TestCSSContentString extends GenericTest {
  function testCSContentStringNewline() {
    $content =& ValueContent::parse('"Sample\
 Content"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample Content");
  }

  function testCSContentStringNewline2() {
    $content =& ValueContent::parse('"Sample\
\
 Content"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample Content");
  }

  function testCSSContentStringEscape6DigitsSpace() {
    $content =& ValueContent::parse('"Sample\00000A Content"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample\nContent");
  }

  function testCSSContentStringEscape6DigitsSpaces() {
    $content =& ValueContent::parse('"Sample\00000A   Content"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample\n  Content");
  }

  function testCSSContentStringEscape6DigitsNoSpace() {
    $content =& ValueContent::parse('"Sample\00000ALine"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample\nLine");
  }

  function testCSSContentStringEscape6DigitsNoSpaceHexadecimal() {
    $content =& ValueContent::parse('"Sample\00000AContent"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample\nContent");
  }

  function testCSSContentStringEscapeSpace() {
    $content =& ValueContent::parse('"Sample\A Content"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample\nContent");
  }

  function testCSSContentStringEscapeSpaces() {
    $content =& ValueContent::parse('"Sample\A   Content"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample\n  Content");
  }

  function testCSSContentStringEscapeNoSpace() {
    $content =& ValueContent::parse('"Sample\ALine"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "Sample\nLine");
  }

  function testCSSContentStringEscapeNoSpaceHex() {
    $content =& ValueContent::parse('"Sample\4Content"');

    $counters =& new CSSCounterCollection();
    $rendered = $content->render($counters);

    $this->assertEqual($rendered, "SampleLontent");
  }
}

?>