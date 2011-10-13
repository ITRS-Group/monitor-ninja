<?php
# $Id: borders13.php 1001 2011-08-08 02:22:55Z lbayuk $
# Plot and image borders - case 13
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plotborder' => array('bottom'),     # Plot border type or NULL to skip
  'pbcolor' => 'cyan',        # Grid color, used for plot border
  'imageborder' => 'solid',    # Image border type or NULL to skip
  'ibwidth' => 2,            # Image border width
  );
require 'borders.php';
