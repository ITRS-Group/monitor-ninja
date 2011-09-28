<?php
# $Id: borders17.php 1001 2011-08-08 02:22:55Z lbayuk $
# Plot and image borders - case 17
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'sides',     # Plot border type or NULL to skip
  'pbcolor' => 'red',        # Grid color, used for plot border
  'imageborder' => 'raised',    # Image border type or NULL to skip
  'ibcolor' => 'blue',        # Image border color
  'ibwidth' => 20            # Image border width
  );
require 'borders.php';
