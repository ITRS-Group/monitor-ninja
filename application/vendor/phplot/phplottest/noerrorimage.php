<?php
# $Id: noerrorimage.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing PHPlot - suppress error image
# This script should produce an error on stderr, but no error image.
require_once 'phplot.php';

$p = new PHPlot;
$p->SetFailureImage(False);
$p->SetPlotType('Nosuchtype');
echo "Script should not reach this point!\n";
