<?php
# $Id: labelvars01.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing phplot - tick/data label variant formatting - case 01
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'YT @ 45d',    # Title line 2
  'y_angle' => 45,          # Y Label angle, NULL to skip
  );
require 'labelvars.php';
