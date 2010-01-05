<?php

class TestImgAlign extends GenericTest {
  function TestImgAlign1() {
    $tree = $this->runPipeline('
<span id="span" style="background: yellow;">
<span id="text">text</span>
<img id="img" style="background: red;" width="50" height="50">
<span>
');

    $span = $tree->get_element_by_id('span');
    $text = $tree->get_element_by_id('text');
    $img  = $tree->get_element_by_id('img');

    $this->assertTrue($span->get_bottom_margin() <= $text->get_bottom_margin(),
                      sprintf('Span bottom margin (%s) should be less than text bottom margin (%s)',
                              $span->get_bottom_margin(),
                              $text->get_bottom_margin()));
    $this->assertTrue($span->get_bottom_margin() <= $img->get_bottom_margin(),
                      sprintf('Span bottom margin (%s) should be less than image bottom margin (%s)',
                              $span->get_bottom_margin(),
                              $img->get_bottom_margin()));
    $this->assertTrue($span->get_top_margin() >= $text->get_top_margin(),
                      sprintf('Span top margin (%s) should be greater than text top margin (%s)',
                              $span->get_top_margin(),
                              $text->get_top_margin()));
    $this->assertTrue($span->get_top_margin() >= $img->get_top_margin(),
                      sprintf('Span top margin (%s) should be greater than image top margin (%s)',
                              $span->get_top_margin(),
                              $img->get_top_margin()));
    $this->assertTrue($text->get_bottom_margin() <= $img->get_bottom_margin(),
                      sprintf('Text bottom margin (%s) should be less than image bottom margin (%s)',
                              $text->get_bottom_margin(),
                              $img->get_bottom_margin()));
    $this->assertTrue($text->get_top_margin() < $img->get_top_margin(),
                      sprintf('Text top margin (%s) should be less than image top margin (%s)',
                              $text->get_top_margin(),
                              $img->get_top_margin()));
  }

  function TestImgAlign2() {
    $tree = $this->runPipeline('
<span id="span" style="background: yellow;">
<input id="text" value="Search" style="font-size: 12px;" type="submit">
<img id="img" style="background: red;" width="50" height="50">
<span>
');

    $span = $tree->get_element_by_id('span');
    $text = $tree->get_element_by_id('text');
    $img  = $tree->get_element_by_id('img');

    $this->assertTrue($span->get_bottom_margin() <= $text->get_bottom_margin(),
                      sprintf('Span bottom margin (%s) should be less than text bottom margin (%s)',
                              $span->get_bottom_margin(),
                              $text->get_bottom_margin()));
    $this->assertTrue($span->get_bottom_margin() <= $img->get_bottom_margin(),
                      sprintf('Span bottom margin (%s) should be less than image bottom margin (%s)',
                              $span->get_bottom_margin(),
                              $img->get_bottom_margin()));
    $this->assertTrue($span->get_top_margin() >= $text->get_top_margin(),
                      sprintf('Span top margin (%s) should be greater than text top margin (%s)',
                              $span->get_top_margin(),
                              $text->get_top_margin()));
    $this->assertTrue($span->get_top_margin() >= $img->get_top_margin(),
                      sprintf('Span top margin (%s) should be greater than image top margin (%s)',
                              $span->get_top_margin(),
                              $img->get_top_margin()));
    $this->assertTrue($text->get_bottom_margin() <= $img->get_bottom_margin(),
                      sprintf('Text bottom margin (%s) should be less than image bottom margin (%s)',
                              $text->get_bottom_margin(),
                              $img->get_bottom_margin()));
    $this->assertTrue($text->get_top_margin() < $img->get_top_margin(),
                      sprintf('Text top margin (%s) should be less than image top margin (%s)',
                              $text->get_top_margin(),
                              $img->get_top_margin()));
  }
}

?>