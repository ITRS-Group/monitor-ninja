<?php

class TestCSSBackgroundAttachment extends GenericTest {
  function testCSSBackgroundAttachment1() {
    $tree = $this->runPipeline('
<html>
<head>
<style type="text/css">
body { background-attachment: fixed; }
#div1 { background-attachment: fixed; }
#div2 { background-attachment: scroll; }
#div3 {  }
</style>
</head>
<body>
<div id="div1">&nbsp;</div>
<div id="div2">&nbsp;</div>
<div id="div3">&nbsp;</div>
</body>
</html>
');

    $div1 =& $tree->get_element_by_id('div1');
    $this->assertEqual(BACKGROUND_ATTACHMENT_FIXED, $div1->getCSSProperty(CSS_BACKGROUND_ATTACHMENT));

    $div2 =& $tree->get_element_by_id('div2');
    $this->assertEqual(BACKGROUND_ATTACHMENT_SCROLL, $div2->getCSSProperty(CSS_BACKGROUND_ATTACHMENT));

    $div3 =& $tree->get_element_by_id('div3');
    $this->assertEqual(BACKGROUND_ATTACHMENT_SCROLL, $div3->getCSSProperty(CSS_BACKGROUND_ATTACHMENT));

    $this->assertEqual(BACKGROUND_ATTACHMENT_FIXED, $tree->getCSSProperty(CSS_BACKGROUND_ATTACHMENT));
  }
}

?>