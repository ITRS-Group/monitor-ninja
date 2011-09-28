<?php
# $Id: setlegrelw2.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing legend relative position - case world-2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align BC to world (1,8)',           # Title part 2
  'lx' => 0.5, 'ly' => 1,         # Legend box fixed point, relative coords
  'relto' => 'world',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 8,               # Base point, world coords
  );
require 'setlegrel.php';
