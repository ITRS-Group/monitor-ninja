<?php
# $Id: transparentorder4.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test - transparency - truecolor, set background then set transparent
require_once 'phplot.php';
$data = array(array('A', 6), array('B', 4), array('C', 2), array('D', 0));
$p = new PHPlot_truecolor;
$p->SetTitle('Truecolor, Set background color, Set transparent');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitleColor('green'); // For contrast vs black/clear background
$p->SetBackgroundColor('yellow');
$p->SetTransparentColor('yellow');
$p->DrawGraph();
