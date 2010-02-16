<?php

#define("FONT_TAHOMA", 'fonts/tahoma.ttf');
#define("FONT_DEJAVUSANS", 'fonts/DejaVuSans.ttf');
#define("FONT_DEJAVUSANS_CONDENSED", 'fonts/DejaVuSansCondensed.ttf');
#define("FONT_DEJAVUSERIF", 'fonts/DejaVuSerif.ttf');
#define("FONT_DEJAVUSERIF_CONDENSED", 'fonts/DejaVuSerifCondensed.ttf');

#define("FONT_TAHOMA", Kohana::find_file('fonts', 'tahoma', 'ttf'));
#define("FONT_DEJAVUSANS", Kohana::find_file('fonts', 'DejaVuSans', 'ttf'));
#define("FONT_DEJAVUSANS_CONDENSED", Kohana::find_file('vendor', 'mfchart/fonts/DejaVuSansCondensed', false, 'ttf'));
#define("FONT_DEJAVUSERIF", Kohana::find_file('fonts', 'DejaVuSerif', 'ttf'));
#define("FONT_DEJAVUSERIF_CONDENSED", Kohana::find_file('fonts', 'DejaVuSerifCondensed', 'ttf'));


class Chart {

	protected $image; // image with the graph

 	protected $width = 450;	// width of the graph

	protected $height = 180; // height of the graph

	protected $margin_left = 0;	// margin around the graph where the legend is displayed

	protected $margin_bottom = 0;	// margin around the graph where the legend is displayed

	protected $margin_right = 0;	// margin around the graph where the legend is displayed

	protected $margin_top = 0;	// margin around the graph where the legend is displayed

	protected $graph_width;	// graph plot area

	protected $graph_height; // graph plot area

	protected $values = array();	// values for displaying in the graph

	protected $font = FONT_DEJAVUSANS_CONDENSED; // hardcoded for now

	protected $font_size = 8; // GD2 - points (but GD1 - pixels)

	protected $colors = array();	// for colors init		--	items are arrays in format array(hex_color, alpha, allocated object) or array(array(hex_color, alpha, allocated object), ...)

	protected $title = '';

	protected $legend = array(); // legend for the graph

	protected $occupied_areas = array();	// for collision detect when placing some items like legend box or labels for points in line graphs etc.
																				// each item in format array(x1, y1, x2, y2)

	public function __construct($width=NULL, $height=NULL)
	{
		if ($width != NULL)
			$this->width = (int) $width;
		if ($height != NULL)
			$this->height = (int) $height;

		$this->colors['background_color'] = array('#fdfdfd', NULL, NULL); // background of the generated image
		#$this->colors['background_color'] = array('#f6f7f8', NULL, NULL); // background of the generated image
		$this->colors['border_color'] = 		array('#fdfdfd',			 NULL, NULL);	// border of the generated image
		#$this->colors['border_color'] = 		array(NULL,			 NULL, NULL);	// border of the generated image
		$this->colors['font_color'] = 			array('#595959', NULL, NULL);	// values at axis
		$this->colors['font_color2'] =			array('#000000', NULL, NULL);	// legend at axis & legend
		$this->colors['font_color3'] = 			array('#d08a22', NULL, NULL);	// values at bars
		$this->colors['legend_color'] = 		array('#fefefe', NULL, NULL); // background legend color
		$this->colors['shade_color']  = 		array('#666666', 95, 	 NULL);
		$this->colors['shade_color2'] = 		array('#ffffff', NULL, NULL);
		$this->colors['shade_color3'] = 		array('#ffffff', NULL, NULL);
#		$this->colors['shade_color2'] = 		array('#e6e6e6', NULL, NULL);
#		$this->colors['shade_color3'] = 		array('#f0f0f0', NULL, NULL);
	}

	/**
	 *	You can set one margin for all by setting only firs parameter.
	 *	Or you can set vertical and horizontal margin by setting two parameters.
	 *	Or all four...
	 *
	 *	@param	int		left margin
	 *	@param	int		top margin
	 *	@param	int		right margin
	 *	@param	int		bottom margin
	 **/
	public function set_margins($left, $top=NULL, $right=NULL, $bottom=NULL)
	{
		$this->margin_left = $left;
		$this->margin_top = ($top === NULL) ? $this->margin_left : $top;
		$this->margin_right = ($right === NULL) ? $this->margin_left : $right;
		$this->margin_bottom = ($bottom === NULL) ? $this->margin_top : $bottom;
	}

	public function set_font($font)
	{
		$this->font = $font;
	}

	public function set_font_size($size)
	{
		$this->font_size = $size;
	}

	public function set_legend($values)
	{
		$this->legend = $values;
	}

	public function set_color($color_name, $color_value, $alpha=NULL)
	{
		$this->colors[$color][0] = $color_value;
		if ($alpha !== NULL)
			$this->colors[$color][1] = $alpha;
	}

	public function set_title($value)
	{
		$this->title = $value;
	}

	// set the values for the graph
	public function set_data($data, $type=false)
	{

		$labels = array();

		// to float - "check"
		foreach ($data as $key => $row)
		{
			if ($type === 'pie')
				$labels[] = $key;

			if (is_array($row))
			{
				foreach ($row as $k => $v)
					$data[$key][$k] = (float) $v;
			}
			else
			{
				$data[$key] = (float) $row;
			}
		}

		if ($type === 'pie') {
			unset($this->colors['colors']);
			$this->colors['colors'] = reports::get_color_values($labels);
		}

		$this->values = $data;
	}

	// draw image with the graph
	public function draw()
	{
		$this->image = imagecreatetruecolor($this->width, $this->height);
		imagealphablending($this->image, true);

		$this->init_colors();

		// draw background + border
		if ($this->get_color('border_color'))
		{
			imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $this->get_color('border_color')); // draw border
			imagefilledrectangle($this->image, 1, 1, $this->width-2, $this->height-2, $this->get_color('background_color')); // draw background
		}
		else
		{
			imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $this->get_color('background_color')); // draw background
		}

		// draw title
		if (!empty($this->title))
		{
			$box_points = imagettfbbox($this->font_size, 0, $this->font, $this->title);
			$textheight = $box_points[3]-$box_points[5];
			$textwidth = $box_points[4]-$box_points[6];
			utilities::imagestringbox($this->image, $this->font, $this->font_size, 0, $this->margin_top, $this->width, $this->margin_top+$textheight, ALIGN_CENTER, VALIGN_MIDDLE, 0, $this->title, $this->get_color('font_color2'));
			$this->margin_top += $textheight + 10;

			$this->add_occupied($this->width/2-$textwidth/2, $this->margin_top, $this->width/2+$textwidth/2, $this->margin_top+$textheight);
		}
	}

	// get the soruce of the image
	public function get_image($type = 'png')
	{
		ob_start();
		switch ($type)
		{
			case 'png':
				imagepng($this->image);
				break;

			case 'jpg':
			case 'jpeg':
				imagejpeg($this->image, '', 0.7);
				break;

			case 'gif':
				imagegif($this->image);
				break;

			case 'wbmp':
				imagewbmp($this->image);
				break;
	  }
	  $img = ob_get_contents();
		ob_end_clean();

	  return $img;
	}

  // display the graph
  public function display()
	{
		if (function_exists("imagepng"))
		{
      header("Content-type: image/png");
      echo $this->get_image('png');
    }
    elseif (function_exists("imagejpeg"))
		{
      header("Content-type: image/jpeg");
      echo $this->get_image('jpg');
    }
		elseif (function_exists("imagegif"))
		{
      header("Content-type: image/gif");
      echo $this->get_image('gif');
    }
    elseif (function_exists("imagewbmp"))
		{
      header("Content-type: image/vnd.wap.wbmp");
      echo $this->get_image('wbmp');
    }
		else
		{
      throw new Exception("Doh! No graphical functions on this server?");
    }

    return true;
  }

  public function set_width($w)
  {
  	$this->width = $w;
  }

  public function set_height($h)
  {
  	$this->height = $h;
  }


	// returns size of the graph plot area
	protected function get_plot_area()
	{
		return array(
			$this->width - $this->margin_left - $this->margin_right, // width
			$this->height - $this->margin_top - $this->margin_bottom	// height
		);
	}

	protected function get_color($key, $i=0, $j=2)
	{
		if (!isset($this->colors[$key]))
			return FALSE;

		$i = $i % count($this->colors[$key]);	// if there is not enough colors get it in cycle

		if ($this->colors[$key][$i][$j] === NULL)
			return FALSE;

		return $this->colors[$key][$i][$j];
	}


	// init colors
	private function init_colors()
	{
		foreach ($this->colors as $key => $values)
		{
			if (!is_array($values[0]))
				$this->colors[$key] = $values = array($values);

			foreach ($values as $i => $value)
			{
				if ($value[0] === NULL)
					continue;

				$rgb = utilities::hex2rgb($value[0]);

				if ($value[1] === NULL)
					$this->colors[$key][$i][2] = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);
				else
					// alpha
					$this->colors[$key][$i][2] = imagecolorallocatealpha($this->image, $rgb[0], $rgb[1], $rgb[2], $value[1]);
			}
		}
	}

	/**
	 *	Draw the legend box.
	 *	If position of the box isn't set manually it's trying to determine position automatically according to already occupied areas.
	 *
	 *	@param		str		Color of the box background.
	 *	@param		int		Relative size of font in points. The font size. Depending on your version of GD, this should be specified as the pixel size (GD1) or point size (GD2).
	 *	@param		int		Position x of the top left of the box.
	 *	@param		int		Position y of the top left of the box.
	 */
	protected function draw_legend($color_index, $relative_font_size=-1, $position_x=NULL, $position_y=NULL)
	{
		if (empty($this->legend))
			return;

		$font_legend = ($relative_font_size<0 AND $this->font_size<abs($relative_font_size)) ? $this->font_size : $this->font_size+$relative_font_size;

		$maxwidth = 0;
		$maxheight = 0;
		foreach ($this->legend as $l)
		{
			$box_points = imagettfbbox($font_legend, 0, $this->font, $l);
			$width = $box_points[4]-$box_points[6];
			$height = $box_points[3]-$box_points[5];
			if ($maxwidth < $width)
				$maxwidth = $width;
			if ($maxheight < $height)
				$maxheight = $height;
		}
		$maxheight += $maxheight*0.3; // line spacing

		$border = $maxheight*0.5;

		if ($position_x === NULL AND $position_y === NULL)
		{
			$from = array($this->margin_left+5, $this->margin_top-10 > 0 ? $this->margin_top-10 : 0);
			$to = array($this->width, $this->height-$this->margin_bottom);
			$found = $this->place_in_free($maxwidth+$border*2+10, count($this->legend)*$maxheight+$border*2, 5, $from, $to);
			$position_x = $found[0];
			$position_y = $found[1];
		}

		$legend_x1 = $position_x;
		$legend_x2 = $position_x + $maxwidth + $border*2 + 10;
		$legend_y1 = $position_y;
		$legend_y2 = $position_y + count($this->legend)*$maxheight + $border*2;

// 			$legend_x1 = $this->width-$maxwidth-40;
// 			$legend_x2 = $legend_x1 + $maxwidth + 30;
// 			$legend_y1 = $this->margin_top-10;
// 			$legend_y2 = $legend_y1 + count($this->legend)*$maxheight + 10;

		utilities::imagefillroundedrect($this->image, $legend_x1+2, $legend_y1+2, $legend_x2+2, $legend_y2+2, 5, $this->get_color('shade_color3'));
		utilities::imagefillroundedrect($this->image, $legend_x1+1, $legend_y1+1, $legend_x2+1, $legend_y2+1, 5, $this->get_color('shade_color2'));
		utilities::imagefillroundedrect($this->image, $legend_x1, $legend_y1, $legend_x2, $legend_y2, 5, $this->get_color('legend_color'));

		$i = 0;
		foreach ($this->legend as $l)
		{
			$y = $legend_y1 + 5 + $maxheight*$i;

			imagefilledrectangle($this->image, $legend_x1+$border, $y+$maxheight/2-2, $legend_x1+$border+5, $y+$maxheight/2+3, $this->get_color($color_index, $i));
			utilities::imagestringbox($this->image, $this->font, $font_legend, $legend_x1+$border+10, $y, $legend_x2, $y+$maxheight, ALIGN_LEFT, VALIGN_MIDDLE, 0, $l, $this->get_color('font_color2'));

			$i++;
		}
	}

	protected function add_occupied($x1, $y1, $x2, $y2)
	{
		// order it
		if ($x1 > $x2)
			list($x1, $x2) = array($x2, $x1);
		if ($y1 > $y2)
			list($y1, $y2) = array($y2, $y1);

		$this->occupied_areas[] = array(
				round($x1),
				round($y1),
				round($x2),
				round($y2)
			);
	}

	/**
	 *	 Detect collision of two boxes. Try to find free place where the box could be placed.
	 *	 Searching in columns priority now. Returns the solution which is the closest to the top or bottom border in the first column where some solution has beend found.
	 *	 It's brute-force. I think it could be optimised when some ordering is used for $occupied_areas.
	 *	 Supports only rectangles aligned with x and y axis.
	 *
	 *		NOTE: For use also with rotated rectangles (for lines for example) look at "separating axis test" at Internet and implement it :)
	 *					http://en.wikipedia.org/wiki/Separating_axis_theorem
	 *					http://board.flashkit.com/board/showthread.php?t=787281
	 *
	 *	@param		int 						Width of the box.
	 *	@param		int 					 	Height of the box.
	 *	@param		int							Padding from the collision boxes. Working correctly only for the last found collision now :((
	 *	@param		array						Where should the searching start? Point [x,y].
	 *	@param		array						Where should the searching end? Point [x,y].
	 *	@param		[left,right]	 	Direction of searching in x axis.
	 *	@param		[up,down]	 			Direction of searching in y axis.
	 **/
	protected function place_in_free($width, $height, $padding=5, $from=array(0,0), $to=array(NULL,NULL), $direction_x = 'left', $direction_y = 'down')
	{
		$width += $padding*2;	// consider padding
		$height += $padding*2;	// consider padding

		// if it's not specified set it to the right bottom corner of the image
		if ($to[0] === NULL)
			$to[0] = $this->width;
		if ($to[1] === NULL)
			$to[1] = $this->height;

		// order from and to points
		if ($from[0] > $to[0])
			list($from[0], $to[0]) = array($to[0], $from[0]);
		if ($from[1] > $to[1])
			list($from[1], $to[1]) = array($to[1], $from[1]);

		// init searched box
		if ($direction_x == 'left')
		{
			$step_x = -1;
			$x1 = $to[0]-$width;
			$x2 = $to[0];
		}
		else
		{
			$step_x = 1;
			$x1 = $from[0];
			$x2 = $from[0]+$width;
		}
		if ($direction_y == 'up')
		{
			$step_y = -1;
			$y1 = $to[1]-$height;
			$y2 = $to[1];
		}
		else
		{
			$step_y = 1;
			$y1 = $from[1];
			$y2 = $from[1]+$height;
		}

		$found = NULL;
		$intersect = TRUE;
		while ($intersect)
		{
			$intersect = FALSE;
			foreach ($this->occupied_areas as $occupied)	// cycle through all occupied areas - brute-force
			{
				if ($x2>=$occupied[0] AND $x1<=$occupied[2] AND $y1<=$occupied[3] AND $y2>=$occupied[1])
				{	// collision of boxes
					$intersect = TRUE;

					// step for axis y movement
					if ($direction_y == 'down')
						$sy = ($occupied[3]-$y1+1)*$step_y;
					else
						$sy = abs($occupied[1]-$y2+1)*$step_y;

					if ($direction_y == 'down' AND $y2+abs($sy) > $to[1])
					{	// go to the next column
						if ($found !== NULL)	// there is already found solution
							break 2;
						$y1 = $from[1];
						$y2 = $from[1]+$height;
						$x1 += $step_x;
						$x2 += $step_x;
					}
					elseif ($direction_y == 'up' AND $y1-abs($sy) < $from[1])
					{	// go to the next column
						if ($found !== NULL)	// there is already found solution
							break 2;
						$y1 = $to[1]-$height;
						$y2 = $to[1];
						$x1 += $step_x;
						$x2 += $step_x;
					}
					else
					{
						$y1 += $sy;
						$y2 += $sy;
					}

					if ($x1<$from[0] OR $x2>$to[0])	// we are at the borders (left or right) and nothing found, go out
						break 2;

					break;
				}
			}

			if (!$intersect)	// found solution
			{	// store found solution and try to look for better one in this column
				if ($found === NULL)
				{	// first found
					$found = array($x1, $y1, $x2, $y2);
				}
				else
				{	// replace previous solution only if it's more closely to the border (top or bottom)
					$gaps = array($found[1]-$from[1], $to[1]-$found[3], $y1-$from[1], $to[1]-$y2);
					if (($gaps[2]<$gaps[0] AND $gaps[2]<$gaps[1]) OR ($gaps[3]<$gaps[0] AND $gaps[3]<$gaps[1]))
						$found = array($x1, $y1, $x2, $y2);
				}

				// simulate intersection
				$intersect = TRUE;

				// move the box
				if (($direction_y == 'down' AND $y2+$step_y > $to[1]) OR ($direction_y == 'up' AND $y1-$step_y < $from[1])) // border found
					break;

				$y1 += $step_y;
				$y2 += $step_y;
			}
		}

		if ($found !== NULL)	// return found solution
			list($x1, $y1, $x2, $y2) = $found;

		return array($x1, $y1, $x2, $y2);
	}


//	This one is for future purpuses if anything needs to be rewritten. Maybe it would be helpful.
//	There is approach without switching in the code and also with switching. It's badly mixed...
//
//		Switched 'left' and 'right'. Need to be corrected.
//
// 	protected function place_in_free($width, $height, $padding=5, $from=array(0,0), $to=array(NULL,NULL), $direction_x = 'left', $direction_y = 'down')
// 	{
// 		$width += $padding*2;	// consider padding
// 		$height += $padding*2;	// consider padding
//
// 		// if it's not specified set it to the right bottom corner of the image
// 		if ($to[0] === NULL)
// 			$to[0] = $this->width;
// 		if ($to[1] === NULL)
// 			$to[1] = $this->height;
//
// 		// order from and to points
// 		if ($from[0] > $to[0])
// 			list($from[0], $to[0]) = array($to[0], $from[0]);
// 		if ($from[1] > $to[1])
// 			list($from[1], $to[1]) = array($to[1], $from[1]);
//
// 		// init searched box
// 		if ($direction_x == 'right')
// 		{
// 			$step_x = -1;
// 			$x1 = $to[0]-$width;
// 			$x2 = $to[0];
// 		}
// 		else
// 		{
// 			$step_x = 1;
// 			$x1 = $from[0];
// 			$x2 = $from[0]+$width;
// 		}
// 		if ($direction_y == 'up')
// 		{
// 			$step_y = -1;
// 			$y1 = $to[1]-$height;
// 			$y2 = $to[1];
// 		}
// 		else
// 		{
// 			$step_y = 1;
// 			$y1 = $from[1];
// 			$y2 = $from[1]+$height;
// 		}
//
// 		$switch = FALSE;	// used for switching between upper and bottom border so it's searching from the borders to the vertical center
//
// 		$intersect = TRUE;
// 		while ($intersect)
// 		{
// 			$intersect = FALSE;
// 			foreach ($this->occupied_areas as $occupied)	// cycle through all occupied areas - brute-force
// 			{
// 				if ($x2>=$occupied[0] AND $x1<=$occupied[2] AND $y1<=$occupied[3] AND $y2>=$occupied[1])
// 				{	// collision of boxes
// 					$intersect = TRUE;
//
// 					$sx = ($occupied[2]-$occupied[0])*$step_x;	// step for axis x movement
//
// 					// move the box
// 					if ($direction_x == 'left' AND $x2+abs($sx) > $to[0])
// 					{
// 						$x1 = $from[0];
// 						$x2 = $from[0]+$width;
// 						$y1 += $step_y;
// 						$y2 += $step_y;
// 					}
// 					elseif ($direction_x == 'right' AND $x1-abs($sx) < $from[0])
// 					{
// 						$x1 = $to[0]-$width;
// 						$x2 = $to[0];
//
// 						if (($direction_y == 'down' AND !$switch) OR ($direction_y == 'up' AND !$switch))
// 						{	// switch to the bottom border
// 							$gap = $y1-$from[1]; // vertical gap
// 							$y2 = $to[1]-$gap;
// 							$y1 = $y2-$height;
// 						}
// 						elseif (($direction_y == 'down' AND $switch) OR ($direction_y == 'up' AND !$switch))
// 						{	// switch to the upper border
// 							$gap = $to[1]-$y2; // vertical gap
// 							$y1 = $from[0]+$gap;
// 							$y2 = $y1+$height;
// 						}
//
// 						if (round($gap) == round($height/2))	// we are at the vertical center and nothing has been found
// 							break 2;
//
// 						if ($switch)	// switch to the other side (switching between top and down and going to the vertical center)
// 						{
// 							$y1 += $step_y;
// 							$y2 += $step_y;
// 						}
// 						$switch = !$switch;
// 					}
// 					else
// 					{
// 						$x1 += $sx;
// 						$x2 += $sx;
// 					}
//
// 					// if we don't have a way to go	--	this is useless now when searching from borders to the vertical center
// // 					if (($direction_y == 'up' AND $y1 <= $from[1]) OR ($direction_y == 'down' AND $y2 >= $to[1]))
// // 						break 2;
//
// 					break;
// 				}
// 			}
// 		}
//
// // 		if ($intersect)	// nothing found
// // 			return $base;
//
// 		return array($x1, $y1, $x2, $y2);
// 	}

}