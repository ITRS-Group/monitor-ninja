<?php
# $Id: borders14.php 1001 2011-08-08 02:22:55Z lbayuk $
# Plot and image borders - case 14
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => array('right'),     # Plot border type or NULL to skip
  'pbcolor' => 'blue',        # Grid color, used for plot border
  'imageborder' => 'solid',    # Image border type or NULL to skip
  'ibcolor' => 'green',        # Image border color
  'ibwidth' => 4,            # Image border width
  );
require 'borders.php';
