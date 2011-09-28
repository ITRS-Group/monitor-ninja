<?php
# $Id: labelvars02.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing phplot - tick/data label variant formatting - case 02
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'YD @ -45d',    # Title line 2
  'yd_angle' => -45,       # Y Data Label angle, NULL to skip
  );
require 'labelvars.php';
