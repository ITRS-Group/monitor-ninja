<?php
# $Id: borders02.php 1001 2011-08-08 02:22:55Z lbayuk $
# Plot and image borders - case 02
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'sides',     # Plot border type or NULL to skip
  'pbcolor' => 'green',        # Grid color, used for plot border
  'imageborder' => 'none',    # Image border type or NULL to skip
  'ibcolor' => 'red',        # Image border color
  );
require 'borders.php';
