<?php
// $Header: /cvsroot/html2ps/css.page-break-before.inc.php,v 1.1.2.1 2006/11/16 03:19:36 Konstantin Exp $

class CSSPageBreakBefore extends CSSPageBreak {
  function get_property_code() {
    return CSS_PAGE_BREAK_BEFORE;
  }

  function get_property_name() {
    return 'page-break-before';
  }
}

CSS::register_css_property(new CSSPageBreakBefore);

?>