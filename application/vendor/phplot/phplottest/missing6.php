<?php
# $Id: missing6.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Lines with missing data - 6
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (Missing first/last, DrawBrokenLines)",   # Title part 2
  'DBLines' => True,        # DrawBrokenLines: True or False or NULL to omit
  'xmiss1' => 0,         # First X (0-14 or NULL) for which Y data is missing
  'xmiss2' => 14,        # Second X (0-14 or NULL) for which Y data is missing
  );
require 'missing.php';
