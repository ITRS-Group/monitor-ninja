<?php
# $Id: labelvars15.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing phplot - tick/data label variant formatting - case 15
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Case 6: XT=n/a, XD=none',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => NULL,         # X Tick Label position, NULL to skip
  'xd_pos' => 'none',       # X Data Label position, NULL to skip
  );
require 'labelvars.php';
