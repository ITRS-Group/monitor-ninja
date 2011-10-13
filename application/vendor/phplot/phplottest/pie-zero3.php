<?php
# $Id: pie-zero3.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Bug 1827263, spoiled chart if close to zero - case 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " shaded",   # Title part 2
  'case' => 3,              # Data test case
  );
require 'pie-zero.php';
