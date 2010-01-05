<?php

class TestNoteCall extends UnitTestCase {
  function runPipeline($html) {
    $pipeline = PipelineFactory::create_default_pipeline("", "");
    $pipeline->configure(array('scalepoints' => false));

    $pipeline->fetchers = array(new MyFetcherMemory($html, ""));
    $pipeline->data_filters[] = new DataFilterHTML2XHTML();
    $pipeline->destination = new DestinationFile("test.pdf");

    parse_config_file('../html2ps.config');
    $media = Media::predefined("A5");
    $pipeline->_prepare($media);
    return $pipeline->_layout_item("", $media, 0, $context, $positioned_filter);
  }

  function testNoteCallWidthClean() {
    $tree = $this->runPipeline('
<html><head><style>
.footnote {
	position: footnote;
}
</style>
</head><body>
<p id="p" style="text-align: justify;">
TEXT
TEXT TEXT TEXT TEXT TEXT TEXT
TEXT TEXT TEXT TEXT TEXT TEXT
TEXT TEXT TEXT TEXT TEXT TEXT
TEXT TEXT TEXT TEXT TEXT TEXT
</p>
</body></html>
');

    $p = $tree->get_element_by_id('p');
    $content = $p->content[0];

    $max_right = $p->get_left();
    foreach ($content->content as $text) {
      $max_right = max($max_right, $text->get_right());
    };
    
    $this->assertTrue($max_right < $p->get_right(),
                      sprintf('Right edge of paragraph content (%s) is greater than paragraph right edge (%s)', 
                              $max_right,
                              $p->get_right()));
  }


  function testNoteCallWidth() {
    $tree = $this->runPipeline('
<html><head><style>
.footnote {
	position: footnote;
}
</style>
</head><body>
<p id="p" style="text-align: justify;">
TEXT <span class="footnote">FOOTNOTE</span>
TEXT TEXT TEXT TEXT TEXT TEXT
TEXT TEXT TEXT TEXT TEXT TEXT
TEXT TEXT TEXT TEXT TEXT TEXT
TEXT TEXT TEXT TEXT TEXT TEXT
</p>
</body></html>
');

    $p = $tree->get_element_by_id('p');
    $content = $p->content[2];

    $max_right = $p->get_left();
    foreach ($content->content as $text) {
      $max_right = max($max_right, $text->get_right());
    };
    
    $this->assertTrue($max_right < $p->get_right(),
                      sprintf('Right edge of paragraph content (%s) is greater than paragraph right edge (%s)', 
                              $max_right,
                              $p->get_right()));
  }
}

?>