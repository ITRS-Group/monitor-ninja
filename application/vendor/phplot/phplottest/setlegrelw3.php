<?php
# $Id: setlegrelw3.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing legend relative position - case world-3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align CR to world (1,8)',           # Title part 2
  'lx' => 1, 'ly' => 0.5,         # Legend box fixed point, relative coords
  'relto' => 'world',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 8,               # Base point, world coords
  );
require 'setlegrel.php';
