<?php
# $Id: mixedsignstackedbar02.php 1001 2011-08-08 02:22:55Z lbayuk $
# Stacked Bars - vertical, bipolar values
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nVertical, Positive/Negative values",  # Title part 2
  'signedness' => 0,   # 1:All >0, -1: All <0; 0: Both >0 and <0 data values
  );
require 'mixedsignstackedbar.php';
