<?php
# $Id: borders03.php 1001 2011-08-08 02:22:55Z lbayuk $
# Plot and image borders - case 03
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'left',     # Plot border type or NULL to skip
  'pbcolor' => 'cyan',        # Grid color, used for plot border
  'ibcolor' => 'red',        # Image border color
  );
require 'borders.php';
