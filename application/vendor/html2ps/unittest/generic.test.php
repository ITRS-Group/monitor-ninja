<?php

class GenericTest extends UnitTestCase {
  function layoutPipeline($html, &$pipeline, &$media, &$context, &$positioned_filter) {
    $pipeline->clear_box_id_map();
    $pipeline->fetchers = array(new MyFetcherMemory($html, realpath(dirname(__FILE__))),
                                new FetcherURL());
    return $pipeline->_layout_item("", $media, 0, $context, $positioned_filter);
  }

  function &preparePipeline(&$media) {
    $pipeline = PipelineFactory::create_default_pipeline("", "");
    $pipeline->configure(array('scalepoints' => false));

    $pipeline->data_filters[] = new DataFilterHTML2XHTML();
    $pipeline->destination = new DestinationFile("test.pdf");

    $pipeline->_prepare($media);
    return $pipeline;
  }

  function runPipeline($html, &$media = null, &$pipeline = null, &$context = null, &$postponed = null) {
    parse_config_file('../html2ps.config');

    if (is_null($media)) {
      $media = Media::predefined("A4");
    };

    $pipeline = $this->preparePipeline($media);
    $tree = $this->layoutPipeline($html, $pipeline, $media, $context, $postponed);
    return $tree;
  }
}
?>