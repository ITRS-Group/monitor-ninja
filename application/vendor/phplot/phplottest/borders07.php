<?php
# $Id: borders07.php 1001 2011-08-08 02:22:55Z lbayuk $
# Plot and image borders - case 07
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => 'bottom',     # Plot border type or NULL to skip
  'pbcolor' => 'cyan',        # Grid color, used for plot border
  'imageborder' => 'plain',    # Image border type or NULL to skip
  'ibwidth' => 2,            # Image border width
  );
require 'borders.php';
