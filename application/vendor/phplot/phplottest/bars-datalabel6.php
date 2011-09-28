<?php
# $Id: bars-datalabel6.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Bars with datalabel - 6
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " 4x4, big font",       # Title part 2
  'ngroups' => 4,           # Number of bar groups
  'nbars' => 4,             # Number of bars per group
  'fontsize' => 5,          # Label font size or NULL to omit
  'shading' => NULL,        # SetShading: 0 or pixels or NULL to omit
  );
require 'bars-datalabel.php';
