<?php

class TestAutofixUrl extends GenericTest {
  function TestAutofixUrlClean1() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'http://www.test.com/test%20test';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($source_url, $fixed_url);
  }

  function TestAutofixUrlClean2() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'http://www.test.com/testtest';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($source_url, $fixed_url);
  }

  function TestAutofixUrlClean3() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'http://user:password@www.test.com/testtest?param=value&param2=value2#fragment';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($source_url, $fixed_url);
  }

  function TestAutofixUrlClean4() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'http://user:password@www.test.com/test%20%01test?param=value&param2=value2#fragment';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($source_url, $fixed_url);
  }

  function TestAutofixUrlClean5() {
    $url_autofix = new AutofixUrl();
    
    $source_url = '/test%20%01test?param=value&param2=value2#fragment';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($source_url, $fixed_url);
  }

  function TestAutofixUrlClean6() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'test%20%01test?param=value&param2=value2#fragment';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($source_url, $fixed_url);
  }

  function TestAutofixUrlClean7() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'test%A1test';
    $expected_url = 'test%A1test';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }

  function TestAutofixUrlDirty1() {
    $url_autofix = new AutofixUrl();
    
    $source_url   = 'http://www.test.com/test  test?test=test&extra=sample';
    $expected_url = 'http://www.test.com/test%20%20test?test=test&extra=sample';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }

  function TestAutofixUrlDirty2() {
    $url_autofix = new AutofixUrl();
    
    $source_url   = ':///!!!!!!????hvkjslgjfg 7357%& (#Q&% (#&%(* gidfg lw.test.com/test  test?test=test&extra=sample';
    $expected_url = '';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }

  function TestAutofixUrlDirty3() {
    $url_autofix = new AutofixUrl();
    
    $source_url   = 'http://user:password@www.test.com/test test?param=value&param2=value2#fragment';
    $expected_url = 'http://user:password@www.test.com/test%20test?param=value&param2=value2#fragment';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }

  function TestAutofixUrlDirty4() {
    $url_autofix = new AutofixUrl();
    
    $source_url = '/test  test?param=value&param2=value2#fragment';
    $expected_url = '/test%20%20test?param=value&param2=value2#fragment';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }

  function TestAutofixUrlDirty5() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'test  test?param=value&param2=value2#fragment';
    $expected_url = 'test%20%20test?param=value&param2=value2#fragment';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }

  function TestAutofixUrlDirty6() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'test%test';
    $expected_url = 'test%25test';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }

  function TestAutofixUrlDirty7() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'test%1test';
    $expected_url = 'test%251test';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }

  function TestAutofixUrlDirty8() {
    $url_autofix = new AutofixUrl();
    
    $source_url = 'test%z1test';
    $expected_url = 'test%25z1test';
    $fixed_url  = $url_autofix->apply($source_url);
    $this->assertEqual($expected_url, $fixed_url);
  }
}


?>