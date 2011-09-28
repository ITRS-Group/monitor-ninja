<?php
# $Id: missing4.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Lines with missing data - 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (linepoints, No BrokenLines)",   # Title part 2
  'DBLines' => False,       # DrawBrokenLines: True or False or NULL to omit
  'PType' => 'linepoints',  # Plot Type: lines, linepoints, squared
  );
require 'missing.php';
