<?php

require_once(HTML2PS_DIR.'utils_units.php');

function cmp_footnote_locations($a, $b) {
  if ($a->get_location() == $b->get_location()) { return 0; };
  return ($a->get_location() > $b->get_location()) ? -1 : 1; 
}

class FootnoteLocation {
  var $_location;
  var $_content_height;

  function FootnoteLocation($location, $content_height) {
    $this->_location       = $location;
    $this->_content_height = $content_height;
  }

  function get_location() {
    return $this->_location;
  }

  function get_content_height() {
    return $this->_content_height;
  }
}

function cmp_page_break_locations($a, $b) {
  if ($a->location == $b->location) { return 0; };
  return ($a->location > $b->location) ? -1 : 1; 
}

class PageBreakLocation {
  var $location;
  var $penalty;

  function PageBreakLocation($location, $penalty) {
    $this->location = round($location,2);
    $this->penalty  = $penalty;
  }

  function get_footnotes_height($footnotes, $page_start, $location) {
    $i = 0;
    $size = count($footnotes);

    $height = 0;

    while ($i < $size && $footnotes[$i]->get_location() > $page_start) { 
      $i++; 
    };

    $footnotes_count = 0;
    while ($i < $size && $footnotes[$i]->get_location() > $location) { 
      $height += $footnotes[$i]->get_content_height();
      $footnotes_count ++;
      $i++;
    };

    if ($footnotes_count > 0) {
      return 
        $height + 
        FOOTNOTE_LINE_TOP_GAP + 
        FOOTNOTE_LINE_BOTTOM_GAP + 
        FOOTNOTE_GAP * ($footnotes_count-1);
    } else {
      return 0;
    };
  }

  function get_penalty($page_start, $max_page_height, $footnotes) {
    $height_penalty = $this->get_page_break_height_penalty($page_start, 
                                                           $max_page_height - $this->get_footnotes_height($footnotes, 
                                                                                                          $page_start, 
                                                                                                          $this->location));

    return $this->penalty + $height_penalty;
  }

  /**
   * We should avoid page breaks  resulting in too much white space at
   * the  page  bottom.  This  function  calculates  a  'penalty'  for
   * breaking page at its current height.
   */
  function get_page_break_height_penalty($page_start, $max_page_height) {
    $current_height = $page_start - $this->location;

    if ($current_height > $max_page_height) {
      return MAX_PAGE_BREAK_PENALTY;
    };

    $free_space = $max_page_height - $current_height;
    $free_space_fraction = $free_space / $max_page_height;

    if ($free_space_fraction < MAX_UNPENALIZED_FREE_FRACTION) { 
      return 0; 
    };

    if ($free_space_fraction > MAX_FREE_FRACTION) {
      return MAX_PAGE_BREAK_PENALTY;
    };

    return 
      ($free_space_fraction - MAX_UNPENALIZED_FREE_FRACTION) / 
      (MAX_FREE_FRACTION    - MAX_UNPENALIZED_FREE_FRACTION) * 
      MAX_PAGE_BREAK_HEIGHT_PENALTY;
  }
}

/**
 * Note that, according to CSS 2.1:
 * 
 * A potential page break  location is typically under the influence
 * of  the   parent  element's  'page-break-inside'   property,  the
 * 'page-break-after'  property of  the preceding  element,  and the
 * 'page-break-before' property of the following element. When these
 * properties have  values other  than 'auto', the  values 'always',
 * 'left', and 'right' take precedence over 'avoid'.
 * 
 * AND
 * 
 * A conforming user agent may interpret the values 'left' and 'right'
 * as 'always'.
 *
 * AND
 * 
 * In the normal flow, page breaks can occur at the following places:
 *
 * 1. In the vertical margin between block boxes. When a page break occurs here, the used values of the relevant 'margin-top' and 'margin-bottom' properties are set to '0'.
 * 2. Between line boxes inside a block box. 
 */
class PageBreakLocator {
  function get_break_locations(&$dom_tree) {
    $locations_ungrouped = PageBreakLocator::get_pages_traverse($dom_tree, 0);

    /**
     * If there's no page break locations (e.g. document is empty)
     * generate one full-size page
     */
    if (count($locations_ungrouped) == 0) { 
      return array();
    };

    return PageBreakLocator::sort_locations($locations_ungrouped);
  }

  function get_footnotes_traverse(&$box) {
    $footnotes = array();

    if (is_a($box, 'BoxNoteCall')) {
      $footnotes[] = new FootnoteLocation($box->get_top_margin(), $box->_note_content->get_full_height());
    } elseif (is_a($box, 'GenericContainerBox')) {
      foreach ($box->content as $child) {
        $footnotes = array_merge($footnotes, PageBreakLocator::get_footnotes_traverse($child));
      };
    };

    return $footnotes;
  }

  function get_pages(&$dom_tree, $max_page_height, $first_page_top) {
    $current_page_top = $first_page_top;
    $heights = array();

    /**
     * Get list of footnotes and heights of footnote content blocks
     */
    $footnotes = PageBreakLocator::get_footnotes_traverse($dom_tree);
    usort($footnotes, 'cmp_footnote_locations');

    $locations = PageBreakLocator::get_break_locations($dom_tree);

    if (count($locations) == 0) {
      return array($max_page_height);
    };

    $best_location = null;
    foreach ($locations as $location) {
      if ($location->location < $current_page_top) {
        if (is_null($best_location)) {
          $best_location = $location;
        };

        $current_pos = round_units($current_page_top - $location->location);
        $available_page_height = round_units($max_page_height - $location->get_footnotes_height($footnotes, $current_page_top, $location->location));

        if ($current_pos > $available_page_height) {
          /**
           * No more locations found on current page
           */

          $best_location_penalty = $best_location->get_penalty($current_page_top, $max_page_height, $footnotes);
          if ($best_location_penalty >= MAX_PAGE_BREAK_PENALTY) {
            error_log('Could not find good page break location');
            $heights[] = $max_page_height;
            $current_page_top -= $max_page_height;
            $best_location = null;
          } else {
            $heights[] = $current_page_top - $best_location->location;
            $current_page_top = $best_location->location;
            $best_location = null;
          };

        } else {
          $location_penalty = $location->get_penalty($current_page_top, $max_page_height, $footnotes);
          $best_penalty = $best_location->get_penalty($current_page_top, $max_page_height, $footnotes);

          if ($location_penalty <= $best_penalty) {
            /**
             * Better page break location found on current page
             */
            $best_location = $location;
          };
        };

        if ($location->penalty < 0) { // Forced page break
          $heights[]        = $current_page_top - $location->location;
          $current_page_top = $location->location;
          $best_location    = null;
        };
      };
    };

    // Last page always will have maximal height
    $heights[] = $max_page_height;

    return $heights;
  }

  function is_forced_page_break($value) {
    return
      $value == PAGE_BREAK_ALWAYS ||
      $value == PAGE_BREAK_LEFT ||
      $value == PAGE_BREAK_RIGHT;
  }

  function has_forced_page_break_before(&$box) {
    return PageBreakLocator::is_forced_page_break($box->get_css_property(CSS_PAGE_BREAK_BEFORE));
  }

  function has_forced_page_break_after(&$box) {
    return PageBreakLocator::is_forced_page_break($box->get_css_property(CSS_PAGE_BREAK_AFTER));
  }

  function get_pages_traverse_block(&$box, &$next, &$previous, $penalty) {
    $locations = array();

    // Absolute/fixed positioned blocks do not cause page breaks
    // (CSS 2.1. 13.2.3 Content outside the page box)
    $position = $box->get_css_property(CSS_POSITION);
    if ($position == POSITION_FIXED || $position == POSITION_ABSOLUTE) {
      return $locations;
    };

    // Fake cell boxes do not generate page break locations
    if (is_a($box, 'FakeTableCellBox')) {
      return $locations;
    }

    /**
     * Check for breaks in block box vertical margin
     */
   
    /**
     * Check for pre-breaks
     */
    if (PageBreakLocator::has_forced_page_break_before($box)) {
      $location = new PageBreakLocation($box->get_top_margin(), FORCED_PAGE_BREAK_BONUS);
    } elseif (!is_null($previous) && $previous->get_css_property(CSS_PAGE_BREAK_AFTER) == PAGE_BREAK_AVOID) {
      $location = new PageBreakLocation($box->get_top_margin(), $penalty + PAGE_BREAK_AFTER_AVOID_PENALTY);
    } elseif ($box->get_css_property(CSS_PAGE_BREAK_BEFORE) == PAGE_BREAK_AVOID) {
      $location = new PageBreakLocation($box->get_top_margin(), $penalty + PAGE_BREAK_BEFORE_AVOID_PENALTY);
    } else {
      $location = new PageBreakLocation($box->get_top_margin(), $penalty);
    };
    $locations[] = $location;

    /**
     * Check for post-breaks
     */
    if (PageBreakLocator::has_forced_page_break_after($box)) {
      $location = new PageBreakLocation($box->get_bottom_margin(), FORCED_PAGE_BREAK_BONUS);
    } elseif (!is_null($next) && $next->get_css_property(CSS_PAGE_BREAK_BEFORE) == PAGE_BREAK_AVOID) { 
      $location = new PageBreakLocation($box->get_bottom_margin(), $penalty + PAGE_BREAK_AFTER_AVOID_PENALTY);
    } elseif ($box->get_css_property(CSS_PAGE_BREAK_AFTER) == PAGE_BREAK_AVOID) {
      $location = new PageBreakLocation($box->get_bottom_margin(), $penalty + PAGE_BREAK_AFTER_AVOID_PENALTY);
    } else {
      $location = new PageBreakLocation($box->get_bottom_margin(), $penalty);
    }
    $locations[] = $location;

    /**
     * Check for breaks inside this box
     * Note that this check should be done after page-break-before/after checks,
     * as 'penalty' value may be modified here
     */
    if ($box->get_css_property(CSS_PAGE_BREAK_INSIDE) == PAGE_BREAK_AVOID) {
      $penalty += PAGE_BREAK_INSIDE_AVOID_PENALTY;
    };        

    /**
     * According to CSS 2.1, 13.3.5 'Best' page breaks, 
     * User agent shoud /Avoid breaking inside a block that has a border/
     *
     * From my point of view, top and bottom borders should not affect page 
     * breaks (as they're not broken by page break), while left and right ones - should.
     */
    $border_left =& $box->get_css_property(CSS_BORDER_LEFT);
    $border_right =& $box->get_css_property(CSS_BORDER_RIGHT);

    $has_left_border = $border_left->style != BS_NONE && $border_left->width->getPoints() > 0;
    $has_right_border = $border_left->style != BS_NONE && $border_left->width->getPoints() > 0;

    if ($has_left_border || $has_right_border) {
      $penalty += PAGE_BREAK_BORDER_PENALTY;
    };

    /**
     * Process box content
     */
    $locations = array_merge($locations, PageBreakLocator::get_pages_traverse($box, $penalty));

    return $locations;
  }

  function get_more_before($base, $content, $size) {
    $i = $base;
    $more_before = 0;

    while ($i > 0) {
      $i--;
      if (is_a($content[$i], 'InlineBox')) {
        $more_before += $content[$i]->get_line_box_count();
      } elseif (is_a($content[$i], 'BRBox') ||
                is_a($content[$i], 'GenericInlineBox')) {
        // Do nothing
      } else {
        return $more_before;
      };
    };

    return $more_before;
  }

  function get_more_after($base, $content, $size) {
    $i = $base;
    $more = 0;

    while ($i < $size-1) {
      $i++;
      if (is_a($content[$i], 'InlineBox')) {
        $more += $content[$i]->getLineBoxCount();
      } elseif (is_a($content[$i], 'BRBox')  ||
                is_a($content[$i], 'GenericInlineBox')) {
        // Do nothing
      } else {
        return $more;
      };
    };

    return $more;
  }

  function get_pages_traverse_table_row(&$box, $penalty) {
    $locations = array();

    $cells = $box->getChildNodes();

    // Find first non-fake (not covered by a table row or cell span) cell
    $i = 0;
    $size = count($cells);
    while ($i < $size &&
           $cells[$i]->is_fake()) {
      $i++;
    };
    // Now $i contains the index of the first content cell or $size of there was no one
    if ($i < $size) {
      $locations[] = new PageBreakLocation($cells[$i]->get_top_margin(),    $penalty);
      $locations[] = new PageBreakLocation($cells[$i]->get_bottom_margin(), $penalty);
    }; 

    $content_watermark = $cells[0]->get_top_margin() - $cells[0]->get_real_full_height();

    /**
     * Process row content
     */
    $inside_penalty = $penalty;
    if ($box->get_css_property(CSS_PAGE_BREAK_INSIDE) == PAGE_BREAK_AVOID) {
      $inside_penalty += PAGE_BREAK_INSIDE_AVOID_PENALTY;
    };        

    $cells = $box->getChildNodes();
    $null = null;
    $ungrouped_row_locations = PageBreakLocator::get_pages_traverse_block($cells[0], 
                                                                          $null, 
                                                                          $null, 
                                                                          $inside_penalty);
    $row_locations = PageBreakLocator::sort_locations($ungrouped_row_locations);

    for ($i=1, $size = count($cells); $i < $size; $i++) {
      $ungrouped_child_locations = PageBreakLocator::get_pages_traverse_block($cells[$i], 
                                                                              $null, 
                                                                              $null, 
                                                                              $inside_penalty);
      $child_locations = PageBreakLocator::sort_locations($ungrouped_child_locations);

      $current_cell_content_watermark = $cells[$i]->get_top_margin() - $cells[$i]->get_real_full_height();

      $new_row_locations = array();

      // Keep only locations available in all cells

      $current_row_location_index = 0;
      while ($current_row_location_index < count($row_locations)) {
        $current_row_location = $row_locations[$current_row_location_index];

        // Check if current row-wide location is below the current cell content;
        // in this case, accept it immediately
        if ($current_row_location->location < $current_cell_content_watermark) {
          $new_row_locations[] = $current_row_location;
        } else {
          // Match all row locations agains the current cell's
          for ($current_child_location_index = 0, $child_locations_total = count($child_locations);
               $current_child_location_index < $child_locations_total;
               $current_child_location_index++) {
            $current_child_location = $child_locations[$current_child_location_index];
            if ($current_child_location->location == $current_row_location->location) {
              $new_row_locations[] = new PageBreakLocation($current_child_location->location,
                                                           max($current_child_location->penalty,
                                                               $current_row_location->penalty));
            };
          };
        };

        $current_row_location_index++;
      };

      // Add locations available below content in previous cells

      for ($current_child_location_index = 0, $child_locations_total = count($child_locations);
           $current_child_location_index < $child_locations_total;
           $current_child_location_index++) {
        $current_child_location = $child_locations[$current_child_location_index];
        if ($current_child_location->location < $content_watermark) {
          $new_row_locations[] = new PageBreakLocation($current_child_location->location,
                                                       $current_child_location->penalty);
        };
      };

      $content_watermark = min($content_watermark, $cells[$i]->get_top_margin() - $cells[$i]->get_real_full_height());

      $row_locations = $new_row_locations;
    };

    $locations = array_merge($locations, $row_locations);
    return $locations;
  }

  function get_pages_traverse_inline(&$box, $penalty, $more_before, $more_after) {
    $locations = array();

    /**
     * Check for breaks between line boxes
     */

    $size = $box->get_line_box_count();    

    if ($size == 0) {
      return $locations;
    };
    
    // If there was  a BR box before current  inline box (indicated by
    // $more_before parameter > 0), we  may break page on the top edge
    // of the first line box
    if ($more_before > 0) {
      if ($more_before < $box->parent->get_css_property(CSS_ORPHANS)) {
        $orphans_penalty = PAGE_BREAK_ORPHANS_PENALTY;
      } else {
        $orphans_penalty = 0;
      };
    
      if ($box->parent->get_css_property(CSS_WIDOWS) > $size + $more_after) {
        $widows_penalty  = PAGE_BREAK_WIDOWS_PENALTY;
      } else {
        $widows_penalty  = 0;
      };

      $line_box = $box->get_line_box(0);
      $locations[] = new PageBreakLocation($line_box->top, 
                                           $penalty + PAGE_BREAK_LINE_PENALTY + $orphans_penalty + $widows_penalty);      
    };

    // If there  was a BR box  after current inline  box (indicated by
    // $more_after parameter >  0), we may break page  on the top edge
    // of the first line box
    if ($more_after > 0) {
      if ($size + 1 + $more_before < $box->parent->get_css_property(CSS_ORPHANS)) {
        $orphans_penalty = PAGE_BREAK_ORPHANS_PENALTY;
      } else {
        $orphans_penalty = 0;
      };
    
      if ($size + 1 + $box->parent->get_css_property(CSS_WIDOWS) > $size + $more_after) {
        $widows_penalty  = PAGE_BREAK_WIDOWS_PENALTY;
      } else {
        $widows_penalty  = 0;
      };

      $line_box = $box->getLineBox($size-1);
      $locations[] = new PageBreakLocation($line_box->bottom, 
                                           $penalty + PAGE_BREAK_LINE_PENALTY + $orphans_penalty + $widows_penalty);      
    };

    // Note that we're  ignoring the last line box  inside this inline
    // box; it is required, as bottom of the last line box will be the
    // same as  the bottom of  the container block box.  Break penalty
    // should be calculated using block-box level data
    for ($i = 0; $i < $size - 1; $i++) {
      $line_box = $box->get_line_box($i);

      if ($i + 1 + $more_before < $box->parent->get_css_property(CSS_ORPHANS)) {
        $orphans_penalty = PAGE_BREAK_ORPHANS_PENALTY;
      } else {
        $orphans_penalty = 0;
      };

      if ($i + 1 + $box->parent->get_css_property(CSS_WIDOWS) > $size + $more_after) {
        $widows_penalty  = PAGE_BREAK_WIDOWS_PENALTY;
      } else {
        $widows_penalty  = 0;
      };

      $locations[] = new PageBreakLocation($line_box->bottom, 
                                           $penalty + PAGE_BREAK_LINE_PENALTY + $orphans_penalty + $widows_penalty);
    };

    return $locations;
  }

  function &get_previous($index, $content, $size) {
    for ($i = $index - 1; $i>=0; $i--) {
      $child = $content[$i];
      if (!$child->is_null()) {
        return $child;
      };
    };

    $dummy = null;
    return $dummy;
  }

  function &get_next($index, &$content, $size) {
    for ($i=$index + 1; $i<$size; $i++) {
      $child =& $content[$i];
      if (!$child->is_null()) {
        return $child;
      };
    };

    $dummy = null;
    return $dummy;
  }

  function get_pages_traverse(&$box, $penalty) {
    if (!is_a($box, 'GenericContainerBox')) { 
      return array(); 
    };

    $locations = array();

    for ($i=0, $content_size = count($box->content); $i<$content_size; $i++) {
      $previous_child =& PageBreakLocator::get_previous($i, $box->content, $content_size);
      $next_child     =& PageBreakLocator::get_next($i, $box->content, $content_size);
      $child          =& $box->content[$i];

      /**
       * Note that page-break-xxx properties apply to block-level elements only
       */
      if (is_a($child, 'BRBox')) {
        // Do nothing
      } elseif ($child->isBlockLevel()) {
        $locations = array_merge($locations, PageBreakLocator::get_pages_traverse_block($child, 
                                                                                        $next_child,
                                                                                        $previous_child,
                                                                                        $penalty));

      } elseif (is_a($child, 'TableCellBox')) {
        $null = null;
        $child_locations = PageBreakLocator::get_pages_traverse_block($child, $null, $null, $penalty);
        $locations = array_merge($locations, $child_locations);
      } elseif (is_a($child, 'InlineBox')) {
        $more_before = 0;
        $more_after  = 0;

        if (is_a($previous_child, 'BRBox')) {
          $more_before = PageBreakLocator::get_more_before($i, $box->content, $content_size);
        };

        if (is_a($next_child, 'BRBox')) {
          $more_after = PageBreakLocator::get_more_after($i, $box->content, $content_size);
        };

        $locations = array_merge($locations, PageBreakLocator::get_pages_traverse_inline($child, $penalty, $more_before, $more_after));
      } elseif (is_a($child, 'TableRowBox')) {
        $locations = array_merge($locations, PageBreakLocator::get_pages_traverse_table_row($child, $penalty));
      };
    };

    return $locations;
  }

  function sort_locations($locations_ungrouped) {
    if (count($locations_ungrouped) == 0) {
      return array();
    };

    usort($locations_ungrouped, 'cmp_page_break_locations'); 

    $last_location = $locations_ungrouped[0];
    $locations = array();
    foreach ($locations_ungrouped as $location) {
      if ($last_location->location != $location->location) {
        $locations[] = $last_location;
        $last_location = $location;
      } else {
        if ($last_location->penalty >= 0 && $location->penalty >= 0) {
          $last_location->penalty = max($last_location->penalty, $location->penalty);
        } else {
          $last_location->penalty = min($last_location->penalty, $location->penalty);
        };
      };
    };
    $locations[] = $last_location;

    return $locations;
  }
}
?>
