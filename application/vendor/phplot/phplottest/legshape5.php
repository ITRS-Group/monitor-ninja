<?php
# $Id: legshape5.php 1001 2011-08-08 02:22:55Z lbayuk $
# Legend shape marker tests - case 5
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Points plot, shape markers, varying shape sizes',  # Title line 2
  'useshapes' => True,     # True for shape markers, false for color boxes
  'plottype' => 'points',   # Plot type, points or linepoints
  'setpointsizes' => True, # True to vary the point shape sizes
  );
require 'legshape.php';
