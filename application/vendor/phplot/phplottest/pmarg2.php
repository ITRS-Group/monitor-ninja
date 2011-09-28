<?php
# $Id: pmarg2.php 1001 2011-08-08 02:22:55Z lbayuk $
# Partial margin specification with SetMarginsPixels - 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nIncrease bottom margin",           # Title part 2
  'doSetMarginsPixels' => True,   # Call SetMarginsPixels?
  'MarginsPixels' => array(NULL,NULL,NULL, 250),  # Args for SetMarginsPixels
  );
require 'pmarg.php';
