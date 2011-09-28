<?php
# $Id: datalabellines2.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Data Label Lines - 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (1 dataset, labels above, lines up)",    # Title part 2
  'groups' => 1,            # Number of data groups (1 or more)
  'labelpos' => 'plotup',     # X Data Label Position: plotup, plotdown, both, none
  'labellines' => True,    # Draw data lines? False or True
  );
require 'datalabellines.php';
