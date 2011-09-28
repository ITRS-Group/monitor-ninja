<?php
# $Id: sb0bug3.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Bug #3296884 Undefined variable with stackedbars (down, 0s, up)
$data = array( array('A', -1, -2), array('B', 0, 0), array('C', 1, 2));
require_once 'phplot.php';
$plot = new PHPlot(400, 300);
$plot->SetTitle('Stacked bar 0 bug: down, 0, up');
$plot->SetPlotType('stackedbars');
$plot->SetDataValues($data);
$plot->SetYDataLabelPos('plotstack');
$plot->DrawGraph();
