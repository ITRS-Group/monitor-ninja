<?php
# $Id: missing_tb.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Plots with missing data - plot type thinbarline
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (thinbarline)",   # Title part 2
  'PType' => 'thinbarline',
  'xmiss1' => 10,
  'xmiss2' => 12,
  );
require 'missing.php';
