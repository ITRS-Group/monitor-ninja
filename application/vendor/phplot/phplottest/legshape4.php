<?php
# $Id: legshape4.php 1001 2011-08-08 02:22:55Z lbayuk $
# Legend shape marker tests - case 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Shape markers, TTF 14pt with extra spacing(8)',  # Title line 2
  'useshapes' => True,     # True for shape markers, false for color boxes
  'fontsize' => 14,       # Use TT font at this size
  'linespacing' => 12,    # Line spacing scale
  'plottype' => 'linepoints',   # Plot type, points or linepoints
  );
require 'legshape.php';
