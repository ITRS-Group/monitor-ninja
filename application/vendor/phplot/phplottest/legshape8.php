<?php
# $Id: legshape8.php 1001 2011-08-08 02:22:55Z lbayuk $
# Legend shape marker tests - case 8
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'TTF 10pt spacing:10, varying size, left/left align',  # Title line 2
  'useshapes' => True,     # True for shape markers, false for color boxes
  'fontsize' => 10,       # Use TT font at this size
  'linespacing' => 10,    # Line spacing scale
  'plottype' => 'points',   # Plot type, points or linepoints
  'setpointsizes' => True, # True to vary the point shape sizes
  'textalign' => 'left',      # Text alignment: left | right, NULL to ignore
                            #  both textalign and colorboxalign.
  'colorboxalign' => 'left',  # Color box alignment: left | right | none
  );
require 'legshape.php';
