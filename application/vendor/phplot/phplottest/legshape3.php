<?php
# $Id: legshape3.php 1001 2011-08-08 02:22:55Z lbayuk $
# Legend shape marker tests - case 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Linepoints, left aligned , markers in legend x2 wide varying sizes',  # Title line 2
  'useshapes' => True,     # True for shape markers, false for color boxes
  'plottype' => 'linepoints',   # Plot type, points or linepoints
  'setpointsizes' => True, # True to vary the point shape sizes
  'textalign' => 'left',      # Text alignment: left | right, NULL to ignore
                            #  both textalign and colorboxalign.
  'colorboxwidth' => 2,  # Color box width horizontal scale adjust
  );
require 'legshape.php';
