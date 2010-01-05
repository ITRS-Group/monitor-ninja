<?php

class MyFetcherMemory extends Fetcher {
  var $base_path;
  var $content;

  function MyFetcherMemory($content, $base_path) {
    $this->content   = $content;
    $this->base_path = $base_path;
  }

  function get_data($url) {
    if (!$url) {
      return new FetchedDataURL($this->content, array(), "");
    } else {
      // remove the "file:///" protocol
      if (substr($url,0,8)=='file:///') {
        $url=substr($url,8);
      };

      $url = urldecode($url);
      return new FetchedDataURL(@file_get_contents($url), array(), "");
    }
  }

  function get_base_url() {
    return 'file:///'.$this->base_path.'/dummy.html';
  }
}

?>