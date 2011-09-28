<?php
# $Id: setlegrelt2.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing legend relative position - case title-2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align TR to title BR',           # Title part 2
  'lx' => 1, 'ly' => 0,         # Legend box fixed point, relative coords
  'relto' => 'title',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 1,               # Base point, relative coords
  );
require 'setlegrel.php';
