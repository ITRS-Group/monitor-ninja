<?php
# $Id: borders15.php 1001 2011-08-08 02:22:55Z lbayuk $
# Plot and image borders - case 15
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'pbcolor' => 'blue',        # Grid color, used for plot border
  'imageborder' => 'plain',    # Image border type or NULL to skip
  'ibcolor' => 'green',        # Image border color
  'ibwidth' => 8,            # Image border width
  );
require 'borders.php';
