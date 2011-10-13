<?php
# $Id: title_text2.php 1001 2011-08-08 02:22:55Z lbayuk $
# Title text tests - 2
# This is a parameterized test. See the script named at the bottom for details.
# Local:
$tp = array(
  'title' => 'Title Text (1 line, spacing 7 (ignored))',  # First or only line
  'title_lines' => 1,       # Number of lines in the main, X, and Y titles
  'line_spacing' => 7,   # Line spacing, or NULL to omit
  );
require 'title_text.php';
