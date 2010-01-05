<?php

class TestRadioPng extends GenericTest {
  function testRadioPngRender() {
    parse_config_file('../html2ps.config');
    $media = Media::predefined("A4");

    $pipeline = $this->preparePipeline($media);
    $pipeline->output_driver = new OutputDriverPng();
    $pipeline->fetchers = array(new MyFetcherMemory('
<html>
<head></head>
<body>
<input type="radio" name="name"/>
</body>
</html>
',''));

    $tree = $pipeline->_layout_item('', $media, 0, $context, $postponed_filter);

    $this->assertNotNull($tree);

    $pipeline->_show_item($tree, 0, $context, $media, $postponed_filter);
  }

  function testCheckedRadioPngRender() {
    parse_config_file('../html2ps.config');
    $media = Media::predefined("A4");

    $pipeline = $this->preparePipeline($media);
    $pipeline->output_driver = new OutputDriverPng();
    $pipeline->fetchers = array(new MyFetcherMemory('
<html>
<head></head>
<body>
<input type="radio" name="name" checked="checked"/>
</body>
</html>
',''));

    $tree = $pipeline->_layout_item('', $media, 0, $context, $postponed_filter);

    $this->assertNotNull($tree);

    $pipeline->_show_item($tree, 0, $context, $media, $postponed_filter);
  }
}

?>