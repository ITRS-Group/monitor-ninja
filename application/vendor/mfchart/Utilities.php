<?php

require('imageSmoothArc.php');


// for text aligning -- imagestringbox
define("ALIGN_LEFT", "left");
define("ALIGN_CENTER", "center");
define("ALIGN_RIGHT", "right");
define("VALIGN_TOP", "top");
define("VALIGN_MIDDLE", "middle");
define("VALIGN_BOTTOM", "bottom");


class Utilities {

	public static function hex2rgb($color)
	{
    if ($color[0] == '#')
      $color = substr($color, 1);

    if (strlen($color) == 6)
      list($r, $g, $b) = array($color[0].$color[1], $color[2].$color[3], $color[4].$color[5]);
    elseif (strlen($color) == 3)
      list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
      return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
	}

	public static function rgb2hex($r, $g=-1, $b=-1)
	{
    if (is_array($r) && sizeof($r) == 3)
      list($r, $g, $b) = $r;

    $r = intval($r); $g = intval($g);
    $b = intval($b);

    $r = dechex($r<0?0:($r>255?255:$r));
    $g = dechex($g<0?0:($g>255?255:$g));
    $b = dechex($b<0?0:($b>255?255:$b));

    $color = (strlen($r) < 2?'0':'').$r;
    $color .= (strlen($g) < 2?'0':'').$g;
    $color .= (strlen($b) < 2?'0':'').$b;

    return '#'.$color;
	}

	// for true type fonts! not for imagestring
	// supports only one line text and vertical direction
	public static function imagestringbox(&$image, $font, $font_size, $left, $top, $right, $bottom, $align, $valign, $leading, $text, $color, $vertical=FALSE)
	{
	 $box_points = imagettfbbox($font_size, 0, $font, $text);
// 	 $box_points[3] = $box_points[3]==-1 ? 0 : $box_points[3];
// 	 $box_points[4] = $box_points[4]==-1 ? 0 : $box_points[4];
// 	 $box_points[5] = $box_points[5]==-1 ? 0 : $box_points[5];
	 $box_points[6] = $box_points[6]==-1 ? 0 : $box_points[6];
	 $textwidth = $box_points[4]-$box_points[6];
	 $textheight = $font_size;

	 if ($vertical)
	 	list($textwidth, $textheight) = array($textheight, $textwidth);

   // Get size of box
   $height = $bottom - $top;
   $width = $right - $left;

//    // Break the text into lines, and into an array
//    if (!$vertical)
//    {
//    	$lines = wordwrap($text, floor($width / imagefontwidth($font)), "\n", true);
//    	$lines = explode("\n", $lines);
// 	 }
// 	 else
// 	 {
// 	 	$lines = wordwrap($text, floor($height / imagefontwidth($font)), "\n", true);
//    	$lines = explode("\n", $lines);
// 	 }

	 $lines = array($text);

   // Other important numbers
   if (!$vertical)
   {
	  $line_height = $textheight + $leading;
		$line_count = floor($height / $line_height);
   }
   else
   {
   	$line_height = $textwidth + $leading;
		$line_count = floor($width / $line_height);
   }
   $line_count = ($line_count > count($lines)) ? (count($lines)) : ($line_count);

   // Loop through lines
   for ($i = 0; $i < $line_count; $i++)
   {
       // Vertical Align
       switch($valign)
       {
           case VALIGN_TOP: // Top
               $y = $top + (($i+1) * $line_height);
               break;
           case VALIGN_MIDDLE: // Middle
               $y = $top + (($height - ($line_count * $line_height)) / 2) + (($i+1) * $line_height);
               break;
           case VALIGN_BOTTOM: // Bottom
               $y = ($top + $height) - ($line_count * $line_height) + (($i+1) * $line_height);
               break;
           default:
               return false;
       }

       // Horizontal Align
       $line_width = $textwidth;
       switch($align)
       {
           case ALIGN_LEFT: // Left
               $x = ($vertical) ? $left + $textwidth : $left;
               break;
           case ALIGN_CENTER: // Center
               $x = ($vertical) ? $left + $width/2 + $textwidth/2 : $left + (($width - $line_width) / 2);
               break;
           case ALIGN_RIGHT: // Right
               $x = ($vertical) ? $right : $left + ($width - $line_width);
               break;
           default:
               return false;
       }

       // Draw
       if ($vertical)
       	imagettftext($image, $font_size, 90, $x, $y, $color, $font, $lines[$i]);
       else
       	imagettftext($image, $font_size, 0, $x, $y, $color, $font, $lines[$i]);
   }

   return true;
	}

	// draw rounded rectangle
	public static function imagefillroundedrect($im, $x, $y, $cx, $cy, $rad, $col)
	{
		// Draw the middle cross shape of the rectangle
	  imagefilledrectangle($im,$x,$y+$rad,$cx,$cy-$rad,$col);
	  imagefilledrectangle($im,$x+$rad,$y,$cx-$rad,$cy,$col);

	  $dia = $rad*2;

		// Now fill in the rounded corners
		imagefilledellipse($im, $x+$rad, $y+$rad, $rad*2, $dia, $col);
	  imagefilledellipse($im, $x+$rad, $cy-$rad, $rad*2, $dia, $col);
	  imagefilledellipse($im, $cx-$rad, $cy-$rad, $rad*2, $dia, $col);
	  imagefilledellipse($im, $cx-$rad, $y+$rad, $rad*2, $dia, $col);
	}

	// draw the background image
	public static function imagefilledrectanglestyled($image, $x1, $y1, $x2, $y2, $bg_style, $color)
	{
		$width = abs($x2-$x1);
		$height = abs($y2-$y1);

 		switch ($bg_style)
 		{
 			case 'dotted':
 				for ($i=0; $i<$width; $i=$i+5)
	 				for ($j=0; $j<$height; $j=$j+5)
	 					imagefilledrectangle($image, $x1+$i, $y1+$j, $x1+$i, $y1+$j, $color); // draw shade of bar
	 			break;
	 		case 'lines':
	 			for ($j=0; $j<$height; $j=$j+1)
	 			{
	 				for ($i=$width-($j%5); $i>0; $i=$i-5)
	 					imagefilledrectangle($image, $x1+$i, $y1+$j, $x1+$i, $y1+$j, $color); // draw shade of bar
	 			}
	 			break;
	 	}
	}

	/**
	 * function imageSmoothAlphaLine() - version 1.0
	 * Draws a smooth line with alpha-functionality
	 *
	 * @param   ident    the image to draw on
	 * @param   integer  x1
	 * @param   integer  y1
	 * @param   integer  x2
	 * @param   integer  y2
	 * @param   integer  red (0 to 255)
	 * @param   integer  green (0 to 255)
	 * @param   integer  blue (0 to 255)
	 * @param   integer  alpha (0 to 127)
	 *
	 * @access  public
	 *
	 * @author  DASPRiD <d@sprid.de>
	 *
	 */
	public static function imagesmoothalphaline($image, $x1, $y1, $x2, $y2, $r, $g, $b, $alpha=0) {
	  $icr = $r;
	  $icg = $g;
	  $icb = $b;
	  $dcol = imagecolorallocatealpha($image, $icr, $icg, $icb, $alpha);

	  if ($y1 == $y2 || $x1 == $x2)
		{
	    imageline($image, $x1, $y1, $x2, $y2, $dcol);
	  }
		else
		{
	    $m = ($y2 - $y1) / ($x2 - $x1);
	    $b = $y1 - $m * $x1;

	    if (abs ($m) <2) {
	      $x = min($x1, $x2);
	      $endx = max($x1, $x2) + 1;

	      while ($x < $endx) {
	        $y = $m * $x + $b;
	        $ya = ($y == floor($y) ? 1: $y - floor($y));
	        $yb = ceil($y) - $y;

	        $trgb = ImageColorAt($image, $x, floor($y));
	        $tcr = ($trgb >> 16) & 0xFF;
	        $tcg = ($trgb >> 8) & 0xFF;
	        $tcb = $trgb & 0xFF;
	        imagesetpixel($image, $x, floor($y), imagecolorallocatealpha($image, ($tcr * $ya + $icr * $yb), ($tcg * $ya + $icg * $yb), ($tcb * $ya + $icb * $yb), $alpha));

	        $trgb = ImageColorAt($image, $x, ceil($y));
	        $tcr = ($trgb >> 16) & 0xFF;
	        $tcg = ($trgb >> 8) & 0xFF;
	        $tcb = $trgb & 0xFF;
	        imagesetpixel($image, $x, ceil($y), imagecolorallocatealpha($image, ($tcr * $yb + $icr * $ya), ($tcg * $yb + $icg * $ya), ($tcb * $yb + $icb * $ya), $alpha));

	        $x++;
	      }
	    } else {
	      $y = min($y1, $y2);
	      $endy = max($y1, $y2) + 1;

	      while ($y < $endy) {
	        $x = ($y - $b) / $m;
	        $xa = ($x == floor($x) ? 1: $x - floor($x));
	        $xb = ceil($x) - $x;

	        $trgb = ImageColorAt($image, floor($x), $y);
	        $tcr = ($trgb >> 16) & 0xFF;
	        $tcg = ($trgb >> 8) & 0xFF;
	        $tcb = $trgb & 0xFF;
	        imagesetpixel($image, floor($x), $y, imagecolorallocatealpha($image, ($tcr * $xa + $icr * $xb), ($tcg * $xa + $icg * $xb), ($tcb * $xa + $icb * $xb), $alpha));

	        $trgb = ImageColorAt($image, ceil($x), $y);
	        $tcr = ($trgb >> 16) & 0xFF;
	        $tcg = ($trgb >> 8) & 0xFF;
	        $tcb = $trgb & 0xFF;
	        imagesetpixel ($image, ceil($x), $y, imagecolorallocatealpha($image, ($tcr * $xb + $icr * $xa), ($tcg * $xb + $icg * $xa), ($tcb * $xb + $icb * $xa), $alpha));

	        $y ++;
	      }
	    }
	  }
	} // end of 'imagesmoothalphaLine()' function

	/**
	 *	almost same as imageSmoothAlphaLine() but supports THICKNESS and DON'T support ALPHA
	 */
	public static function imagesmoothline($image, $x1, $base_y1, $x2, $base_y2, $r, $g, $b, $thickness=1) {
	  $icr = $r;
	  $icg = $g;
	  $icb = $b;
	  $dcol = imagecolorallocate($image, $icr, $icg, $icb);
	  $alpha = 0;

	  if ($base_y1 == $base_y2 || $x1 == $x2)	// 90° line
		{
			imagesetthickness($image, $thickness);
	    imageline($image, $x1, $base_y1, $x2, $base_y2, $dcol);
			imagesetthickness($image, 1);
	  }
		else
		{
			for ($i=0; $i<$thickness; $i++)
			{
				$y1 = $base_y1+$i;
				$y2 = $base_y2+$i;

				if ($thickness > 2)
				{
					$y1 = $base_y1-$thickness/2+$i;
					$y2 = $base_y2-$thickness/2+$i;

					if ($i!=0 AND $i!=$thickness-1)	// inner lines
						imageline($image, $x1, $y1, $x2, $y2, $dcol);
				}


		    $m = ($y2 - $y1) / ($x2 - $x1);
		    $b = $y1 - $m * $x1;

		    if (abs ($m) <2) {
		      $x = min($x1, $x2);
		      $endx = max($x1, $x2) + 1;

		      while ($x < $endx) {
		        $y = $m * $x + $b;
		        $ya = ($y == floor($y) ? 1: $y - floor($y));
		        $yb = ceil($y) - $y;

		        $trgb = ImageColorAt($image, $x, floor($y));
		        $tcr = ($trgb >> 16) & 0xFF;
		        $tcg = ($trgb >> 8) & 0xFF;
		        $tcb = $trgb & 0xFF;
		        imagesetpixel($image, $x, floor($y), imagecolorallocatealpha($image, ($tcr * $ya + $icr * $yb), ($tcg * $ya + $icg * $yb), ($tcb * $ya + $icb * $yb), $alpha));

		        $trgb = ImageColorAt($image, $x, ceil($y));
		        $tcr = ($trgb >> 16) & 0xFF;
		        $tcg = ($trgb >> 8) & 0xFF;
		        $tcb = $trgb & 0xFF;
		        imagesetpixel($image, $x, ceil($y), imagecolorallocatealpha($image, ($tcr * $yb + $icr * $ya), ($tcg * $yb + $icg * $ya), ($tcb * $yb + $icb * $ya), $alpha));

		        $x++;
		      }
		    } else {
		      $y = min($y1, $y2);
		      $endy = max($y1, $y2) + 1;

		      while ($y < $endy) {
		        $x = ($y - $b) / $m;
		        $xa = ($x == floor($x) ? 1: $x - floor($x));
		        $xb = ceil($x) - $x;

		        $trgb = ImageColorAt($image, floor($x), $y);
		        $tcr = ($trgb >> 16) & 0xFF;
		        $tcg = ($trgb >> 8) & 0xFF;
		        $tcb = $trgb & 0xFF;
		        imagesetpixel($image, floor($x), $y, imagecolorallocatealpha($image, ($tcr * $xa + $icr * $xb), ($tcg * $xa + $icg * $xb), ($tcb * $xa + $icb * $xb), $alpha));

		        $trgb = ImageColorAt($image, ceil($x), $y);
		        $tcr = ($trgb >> 16) & 0xFF;
		        $tcg = ($trgb >> 8) & 0xFF;
		        $tcb = $trgb & 0xFF;
		        imagesetpixel ($image, ceil($x), $y, imagecolorallocatealpha($image, ($tcr * $xb + $icr * $xa), ($tcg * $xb + $icg * $xa), ($tcb * $xb + $icb * $xa), $alpha));

		        $y ++;
		      }
		    }

		  }	// end for
	  }
	} // end of 'imagesmoothalphaLine()' function

	// works on multidimensional array
	public static function max($array)
	{
    $return = NULL;

		// use foreach to iterate over our input array.
    foreach( $array as $value ) {
      // check if $value is an array...
      if( is_array($value) ) {
        // ... $value is an array so recursively pass it into multimax() to
        // determine it's highest value.
        $subvalue = utilities::max($value);
        // if the returned $subvalue is greater than our current highest value,
        // set it as our $return value.
        if($return === NULL OR $subvalue > $return) {
          $return = $subvalue;
        }
      } elseif($return === NULL OR $value > $return) {
        // ... $value is not an array so set the return variable if it's greater
        // than our highest value so far.
        $return = $value;
      }
    }

    return $return;	// return (what should be) the highest value from any dimension.
	}

	// works on multidimensional array
	public static function min($array)
	{
    $return = NULL;

		// use foreach to iterate over our input array.
    foreach( $array as $value ) {
      // check if $value is an array...
      if( is_array($value) ) {
        // ... $value is an array so recursively pass it into multimax() to
        // determine it's highest value.
        $subvalue = utilities::min($value);
        // if the returned $subvalue is greater than our current highest value,
        // set it as our $return value.
        if($return === NULL OR $subvalue < $return) {
          $return = $subvalue;
        }
      } elseif($return === NULL OR $value < $return) {
        // ... $value is not an array so set the return variable if it's greater
        // than our highest value so far.
        $return = $value;
      }
    }

    return $return;	// return (what should be) the highest value from any dimension.
	}

	public static function deg2rad($degrees)
	{
		return $degrees / 180.0 * M_PI;
	}
}