<?php
# $Id: borders12.php 1001 2011-08-08 02:22:55Z lbayuk $
# Plot and image borders - case 12
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => array('full'),     # Plot border type or NULL to skip
  'pbcolor' => 'red',        # Grid color, used for plot border
  'imageborder' => 'solid',    # Image border type or NULL to skip
  'ibcolor' => 'green',        # Image border color
  );
require 'borders.php';
