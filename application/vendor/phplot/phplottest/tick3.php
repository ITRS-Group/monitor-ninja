<?php
# $Id: tick3.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot Test - Ticks, Lengths and Labels - skip ticks (1)
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' - Skip bottom, left',           # Title part 2
  'skiptick' => 'BL',          # Skip ticks: NULL or string with BTRL
  );
require 'tick.php';
