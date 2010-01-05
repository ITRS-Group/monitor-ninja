<?php

class TestCSSContent extends GenericTest {
  function testCSSContentWithEscapesInsideHTML() {
    $tree = $this->runPipeline(file_get_contents('test.css.content.1.html'));

    $element0 =& $tree->get_element_by_id('div1');
    $element = $element0->content[1];

    $content =& $element->getCSSProperty(CSS_CONTENT);

    $counters =& new CSSCounterCollection();
    $this->assertEqual($content->render($counters), "&lt;span style=&quot;font-weight: bold;&quot;&gt;My&lt;/span&gt; Page");
  }

  function testCSSContentWithEscapes() {
    $null = null;
    $collection =& parse_css_property('content: "&lt;span style=&quot;font-weight: bold;&quot;&gt;My&lt;/span&gt; Page";', $null);

    $this->assertTrue($collection->contains(CSS_CONTENT));
   
    $counters =& new CSSCounterCollection();
    $content =& $collection->getPropertyValue(CSS_CONTENT);
    $this->assertEqual($content->render($counters), "&lt;span style=&quot;font-weight: bold;&quot;&gt;My&lt;/span&gt; Page");
  }

  function testCSSHTMLContentWithEscapes() {
    $null = null;
    $collection =& parse_css_property('-html2ps-html-content: "&lt;span style=&quot;font-weight: bold;&quot;&gt;My&lt;/span&gt; Page";', $null);

    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));
   
    $counters =& new CSSCounterCollection();
    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $this->assertEqual($content->render($counters), "&lt;span style=&quot;font-weight: bold;&quot;&gt;My&lt;/span&gt; Page");
  }

  function testCSSContentWithCounters() {
    $null = null;
    $collection =& parse_css_property('content: "Page " counter(page) " of " counter(pages);', $null);

    $this->assertTrue($collection->contains(CSS_CONTENT));
   
    $counters =& new CSSCounterCollection();

    $page_counter =& new CSSCounter('page');
    $page_counter->set(10);
    $counters->add($page_counter);

    $sample_counter =& new CSSCounter('sample');
    $sample_counter->set(1);
    $counters->add($sample_counter);

    $content =& $collection->getPropertyValue(CSS_CONTENT);
    $this->assertEqual($content->render($counters), "Page 10 of ");
  }

  function testCSSContentWithCountersAndSemicolons() {
    $null = null;
    $collection =& parse_css_property('content: "Page; " counter(page) " of; " counter(pages);', $null);

    $this->assertTrue($collection->contains(CSS_CONTENT));
   
    $counters =& new CSSCounterCollection();

    $page_counter =& new CSSCounter('page');
    $page_counter->set(1);
    $counters->add($page_counter);

    $sample_counter =& new CSSCounter('pages');
    $sample_counter->set(10);
    $counters->add($sample_counter);

    $content =& $collection->getPropertyValue(CSS_CONTENT);
    $this->assertEqual($content->render($counters), "Page; 1 of; 10");
  }

  function testCSSContentEmptyWithQuotes() {
    $null = null;
    $collection =& parse_css_property('content: "";', $null);

    $this->assertTrue($collection->contains(CSS_CONTENT));
   
    $counters =& new CSSCounterCollection();
    $content =& $collection->getPropertyValue(CSS_CONTENT);
    $this->assertEqual($content->render($counters), "");
  }

  function testCSSContentEmptyWithApostrophes() {
    $null = null;
    $collection =& parse_css_property('content: \'\';', $null);

    $this->assertTrue($collection->contains(CSS_CONTENT));
   
    $counters =& new CSSCounterCollection();
    $content =& $collection->getPropertyValue(CSS_CONTENT);
    $this->assertEqual($content->render($counters), "");
  }

  function testCSSContentEmptyWithOtherProperties() {
    $null = null;
    $collection =& parse_css_properties("-html2ps-html-content: ''; content: ''; width: auto; height: auto; margin: 0; border: none; padding: 0; font: auto;", $null);

    $this->assertTrue($collection->contains(CSS_CONTENT));
    $this->assertTrue($collection->contains(CSS_HTML2PS_HTML_CONTENT));
   
    $counters =& new CSSCounterCollection();

    $content =& $collection->getPropertyValue(CSS_CONTENT);
    $this->assertEqual($content->render($counters), "");

    $content =& $collection->getPropertyValue(CSS_HTML2PS_HTML_CONTENT);
    $this->assertEqual($content->render($counters), "");
  }
}

?>