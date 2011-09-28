<?php
# $Id: missing_b.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Plots with missing data - plot type bars
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (bars)",   # Title part 2
  'PType' => 'bars',
  'xmiss1' => 2,
  'xmiss2' => 8,
  );
require 'missing.php';
