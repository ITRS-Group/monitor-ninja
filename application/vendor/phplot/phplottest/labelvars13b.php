<?php
# $Id: labelvars13b.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing phplot - tick/data label variant formatting - case 13b
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Case 4: XT=up, XD=down',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => 'plotup',       # X Tick Label position, NULL to skip
  'xd_pos' => 'plotdown',       # X Data Label position, NULL to skip
  );
require 'labelvars.php';
