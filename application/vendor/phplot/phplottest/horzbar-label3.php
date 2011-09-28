<?php
# $Id: horzbar-label3.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: horizontal bars with data value labels, case 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nBi-sign data, Number format",       # Title part 2
  'datasign' => 0,              # 1 for all >= 0, -1 for all <= 0, 0 for all
  'labelformat' => True,        # Format data labels ?
  );
require 'horzbar-label.php';
