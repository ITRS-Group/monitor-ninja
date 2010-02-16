<?php

class LinePointChart extends Chart {

	protected $type = NULL;

	protected $point_style = 'circle'; // diamond / circle	-- if point_fill_color is set it will be filled else empty (try it)
	
	protected $point_width = 7;	// size of points
	
	protected $pen_width = 3; // width of the graph lines
	
	protected $approx_line_gap = 40; // how much space should be approximately between each line
	
	protected $plot_bg_color = array('#fff', '#ccc'); // also supports arrays as array('#fff', '#333', '#432', ...) for gradients
	
	protected $plot_bg_by_lines = FALSE; // if set to true bg colors will be rendered in areas separated by lines (useful for gradients)
	
	protected $background_style = 'lines'; // [dotted / lines / NULL]
	
	protected $legend_y = '';	// y axis legend
	
	protected $legend_x = ''; // x axis legend
	
	protected $point_legend = TRUE; // display legend above the points?
	
	protected $vertical_point_lines = TRUE; // display vertical lines in the middle of the points?
	
	
	public function __construct($width=NULL, $height=NULL)
	{
		parent::__construct($width, $height);
		
		$this->set_margins(5);
		
		$this->colors['pen_color'] = array(	// for drawing "value lines"
				array('#c45e31', NULL, NULL),
				array('#b7da2d', NULL, NULL)
			);
		$this->colors['point_color'] = array(	// point border color
				array('#c45e31', NULL, NULL),
				array('#b7da2d', NULL, NULL)
			);
		$this->colors['point_fill_color'] = array('#ffffff', NULL, NULL);	// point fill color
		$this->colors['line_color'] = 			array('#dfdfdf', NULL, NULL);	// primary color of lines
		$this->colors['line_color2'] =			array('#969696', NULL, NULL);	// for short lines by the legend for example
		$this->colors['legend_color'] = 		array('#fefefe', NULL, NULL); // background legend color
	}
	
	public function set_point_legend($value)
	{
		$this->point_legend = $value;
	}
	
	public function set_background_style($value)
	{
		$this->background_style = $value;
	}
	
	public function set_legend_y($value)
	{
		$this->legend_y = $value;
	}
	
	public function set_legend_x($value)
	{
		$this->legend_x = $value;
	}
	
	// it's approximate values because gap will be exactly counted so it's nicely drawn. see ( --gap-line-- )
	public function set_approx_line_gap($value)
	{
		$this->approx_line_gap = $value;
	}
	
	// also supports arays for gradients
	public function set_plot_bg_color($values, $by_lines = FALSE)
	{
		$this->plot_bg_color = $values;
		$this->plot_bg_by_lines = $by_lines;
	}
	
	public function set_point_style($value)
	{
		$this->point_style = $value;
	}
	
	public function draw()
	{
		parent::draw();
		
 		$max_value = utilities::max($this->values);
 		$min_value = utilities::min($this->values);
		
		$max = max($max_value, abs($min_value));
		
		$box_points = imagettfbbox($this->font_size, 0, $this->font, '0');
		$fheight = $box_points[3]-$box_points[5]; // font height
		$vpp = $max/$this->height; // get approximate value per px -- exact value could be get only later so try it this way
		$plus = ceil( $vpp * ($fheight + ($this->point_legend ? $fheight : 0)) * 2.3 );
		
		$max_value += $plus;	// add PLUS so the legend for points and also lines are in the graph borders
		if ($this->type != 'curved')
			$min_value -= $min_value <= 0 ? $plus : 0;	// subtract PLUS so the legend for points and also lines are in the graph borders
		else
			$min_value -= $plus;	// subtract PLUS so the legend for points and also lines are in the graph borders
		
		$max = max($max_value, abs($min_value));
		
		// get the next "nice" value for max
		$pow = 0;
		while (pow(10, ++$pow) < $max);
		$step = pow(10, $pow-2);
		
		if ($max_value > 0)
		{
			$from = round($max_value, -$pow+2);
			if ($from < $max_value)	// if it was rounded down
				$from += $step;
			$max_value = $from;
		}
		else
		{
			$max_value = 0;
		}
		
		// get the prev "nice" value for min
		if ($min_value < 0)
		{
			$from = round(abs($min_value), -$pow+2);
			if ($from < abs($min_value))	// if it was rounded down
				$from += $step;
			$min_value = -$from;
		}
		else
		{
			$min_value = 0;
		}
		
		// -- start: sizes of titles and labels of axis
		// y axis title
		if (!empty($this->legend_y))
		{
			$box_points = imagettfbbox($this->font_size, 0, $this->font, $this->legend_y);
			$y_legend_width = $box_points[3]-$box_points[5]; // y-axis legend height
			$y_legend_gap = $this->margin_left;
			
			$this->margin_left += $y_legend_width*2;	// modify size of the left margin
		}
		
		// y axis labels
		$box_points = imagettfbbox($this->font_size, 0, $this->font, max($max_value, abs($min_value)).'-');
		$y_legend_labels_width = $box_points[4]-$box_points[6]; // y-axis labels width
		$y_legend_labels_height = $box_points[3]-$box_points[5]; // y-axis labels height
		
		$this->margin_left += $y_legend_labels_width + 5;	// modify size of the left margin
		
		// x axis title
		if (!empty($this->legend_x))
		{
			$box_points = imagettfbbox($this->font_size, 0, $this->font, $this->legend_x);
			$x_legend_height = $box_points[3]-$box_points[5];
			$x_legend_gap = $this->margin_bottom;
			
			$this->margin_bottom += $x_legend_height*2 + 2;
		}
		
		// x axis labels
		$box_points = imagettfbbox($this->font_size, 0, $this->font, implode(array_keys($this->values)));
		$x_legend_labels_height = $box_points[3]-$box_points[5]; // y-axis labels height
		
		$this->margin_bottom += $x_legend_labels_height + 5;	// modify size of the left margin
		
		// -- end: sizes of titles and labels of axis
		
		if (!empty($this->legend))
			$this->margin_right += 15;
		
		$plot_sizes = $this->get_plot_area();	// size of the graph area
		
		
		if (($max_value+abs($min_value)) == 0)
			$ratio = $plot_sizes[1];	// default value when there are only zero data
		else
			$ratio = $plot_sizes[1]/($max_value+abs($min_value));	// how much value is for one pixel? -- sorry for the bad English
		
		$horizontal_lines = floor($plot_sizes[1]/$this->approx_line_gap);	// --gap-line--		count exact count of the horizontal lines
		$horizontal_gap = $plot_sizes[1]/$horizontal_lines; // count exact space between these lines
		
		
		// draw graph background
		if (!is_array($this->plot_bg_color))
		{ // draw solid color
			$plot_bg_color_rgb = utilities::hex2rgb($this->plot_bg_color);
			$plot_bg_color = imagecolorallocate($this->image, $plot_bg_color_rgb[0], $plot_bg_color_rgb[1], $plot_bg_color_rgb[2]);
			imagefilledrectangle($this->image, $this->margin_left, $this->margin_top, $this->width-1-$this->margin_right, $this->height-1-$this->margin_bottom, $plot_bg_color);
		}
		else
		{ // draw gradient
			if (!$this->plot_bg_by_lines)
			{	// one for the whole bg
				$gradient_height = $plot_sizes[1]; // how much should gradient take?
				$goesto = 0;
			}
			else
			{ // one for each horizontal gap separated by y axis lines
				$gradient_height = ceil($horizontal_gap); // how much should gradient take?
				$goesto = $horizontal_lines-1;
			}
			for ($i=0; $i<=$goesto; $i++)
			{
				$color_index = ($i*2)%count($this->plot_bg_color);
				$c1 = $this->plot_bg_color[$color_index];	// start color
				$c2 = isset($this->plot_bg_color[$color_index+1]) ? $this->plot_bg_color[$color_index+1] : $this->plot_bg_color[$color_index]; // end color
				$gradient = new Gradient(NULL, $plot_sizes[0], $gradient_height, 'vertical', $c1, $c2, 0);	// make gradient
				imagecopymerge($this->image, $gradient->image, $this->margin_left, $this->margin_top+$gradient_height*$i, 0, 0, $this->width-$this->margin_left-$this->margin_right, $gradient_height, 100); // copy the gradient to the graph
			}
		}
		
		if (!empty($this->legend_y))
			// draw y axis legend
			utilities::imagestringbox($this->image, $this->font, $this->font_size, $y_legend_gap, $this->margin_top, $y_legend_gap+$y_legend_width, $this->height-$this->margin_bottom, ALIGN_CENTER, VALIGN_MIDDLE, 0, $this->legend_y, $this->get_color('font_color2'), TRUE);
		if (!empty($this->legend_x))
			// draw x axis legend
			utilities::imagestringbox($this->image, $this->font, $this->font_size, $this->margin_left, $this->height-$x_legend_gap-$x_legend_height, $this->width-$this->margin_right, $this->height-$x_legend_gap, ALIGN_CENTER, VALIGN_MIDDLE, 0, $this->legend_x, $this->get_color('font_color2'));
		
		$style = array($this->get_color('line_color'), $this->get_color('line_color'), $this->get_color('line_color'), $this->get_color('line_color'), $this->get_color('line_color'), IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT);
		imagesetstyle($this->image, $style); // set style of the line
		
		for ($i=0; $i<=$horizontal_lines; $i++)
		{
			$y = $this->height - $this->margin_bottom - $horizontal_gap * $i;
			imageline($this->image, $this->margin_left, $y, $this->width-$this->margin_right, $y, IMG_COLOR_STYLED);	// draw the line in graph
			imageline($this->image, $this->margin_left-2, $y, $this->margin_left, $y, $this->get_color('line_color2')); // draw the line by the y-axis legend
			
			$v = round($horizontal_gap * $i / $ratio, 1) + $min_value;
			$y_legend_y = $y-round($y_legend_labels_height/2);
			
			utilities::imagestringbox($this->image, $this->font, $this->font_size, $this->margin_left-$y_legend_labels_width, $y_legend_y, $this->margin_left-6, $y_legend_y+$y_legend_labels_height, ALIGN_RIGHT, VALIGN_MIDDLE, 0, $v, $this->get_color('font_color'));
		}
		
		// draw graph borders
		imageline($this->image, $this->margin_left, $this->margin_top, $this->margin_left, $this->height-$this->margin_bottom, $this->get_color('line_color2')); // left
		imageline($this->image, $this->margin_left, $this->height-$this->margin_bottom, $this->width-$this->margin_right, $this->height-$this->margin_bottom, $this->get_color('line_color2')); // bottom
		imageline($this->image, $this->margin_left+1, $this->margin_top, $this->width-$this->margin_right, $this->margin_top, $this->get_color('line_color')); // top
		imageline($this->image, $this->width-$this->margin_right, $this->margin_top, $this->width-$this->margin_right, $this->height-$this->margin_bottom-1, $this->get_color('line_color')); // right
		
		// draw the background image
 		switch ($this->background_style)
 		{
 			case 'dotted':
 				$bgs_color = $this->get_color('shade_color2');
	 		case 'lines':
	 			$bgs_color = $this->get_color('shade_color3');
	 	}
	 	utilities::imagefilledrectanglestyled($this->image, $this->margin_left, $this->margin_top, $this->width-$this->margin_right, $this->height-$this->margin_bottom, $this->background_style, $bgs_color);
 		
 		$zeroline = abs($min_value)*$ratio;	// where should be y=0 placed?
 		imagefilledrectangle($this->image, $this->margin_left, $this->height-$this->margin_bottom-$zeroline, $this->width-$this->margin_right, $this->height-$this->margin_bottom-$zeroline, $this->get_color('shade_color')); // draw shade of bar
 		
 		$this->draw_values($zeroline, $ratio); // draw bars
 		$this->draw_legend('pen_color'); // draw box with the legend
	}

	protected function draw_values($zeroline, $ratio)
	{
		$plot_sizes = $this->get_plot_area();
 		$font_point_legend = $this->font_size < 1 ? $this->font_size : $this->font_size-1;
 		
		$total_groups = count($this->values);	// count of all groups of points
 		$gap = ($plot_sizes[0] - $total_groups) / ($total_groups+1);	// gap between points
		
		// -- draw lines 
		// separated from draw points so points are on top of lines
		
		$previous = array();	// previous points
		$i=0;
		foreach ($this->values AS $key => $v)
		{
			$x = $this->margin_left + $gap + 1 + $i*($gap+1);	// 1 is considered as point width
			$y2 = $this->height - $this->margin_bottom - $zeroline;
			
			if ($this->vertical_point_lines)
				imageline($this->image, $x, $this->margin_top-1, $x, $this->height-$this->margin_bottom-1, IMG_COLOR_STYLED);
			
			foreach ($v as $j => $value)
			{
				$y1 = $y2 - $value*$ratio;
				
				if (!empty($previous))
				{	// draw line
					$px = $x-$gap-1;
					$py = $y2 - $previous[$j]*$ratio;
					
// 					imagesetthickness($this->image, $this->pen_width);
// 					imageline($this->image, $px, $py, $x, $y1, $this->get_color('pen_color', $j));
// 					imagesetthickness($this->image, 1);
					
					$color = utilities::hex2rgb($this->get_color('pen_color', $j, 0));
					utilities::imagesmoothline($this->image, $px, $py, $x, $y1, $color[0], $color[1], $color[2], $this->pen_width);
				}
				
			}
			$previous = $v;
			
			$i++;
		}
		
		// -- draw points
		
		$i=0;
		foreach ($this->values AS $key => $v)
		{
			$x = $this->margin_left + $gap + 1 + $i*($gap+1);	// 1 is considered as point width
			$y2 = $this->height - $this->margin_bottom - $zeroline;
			
			$fheight = $this->font_size; // default - recomputed later in the next cycle
			
			foreach ($v as $j => $value)
			{
				$y1 = $y2 - $value*$ratio;
				
				$this->draw_point($x, $y1, $this->point_style, $this->get_color('point_color', $j), $this->get_color('point_fill_color', $j));
				
				$box_points = imagettfbbox($this->font_size, 0, $this->font, $value);
				$point_legend_hwidth = ($box_points[4]-$box_points[6]) / 2;
				$fheight = $box_points[3]-$box_points[5];
				
				// point legend
				if ($this->point_legend)
				{
					utilities::imagestringbox($this->image, $this->font, $font_point_legend, $x-$point_legend_hwidth, $y1-$fheight-5-$this->point_width/2, $x+$point_legend_hwidth, $y1-$this->point_width/2, ALIGN_CENTER, VALIGN_MIDDLE, 0, $value, $this->get_color('font_color3'));
					$this->add_occupied($x-$point_legend_hwidth, $y1-$fheight-5-$this->point_width/2, $x+$point_legend_hwidth, $y1-$this->point_width/2);
				}
			}
			
			$box_points = imagettfbbox($this->font_size, 0, $this->font, $key);
			$y_legend_hwidth = ($box_points[4]-$box_points[6]) / 2;
			
 			// x axis legend
 			utilities::imagestringbox($this->image, $this->font, $this->font_size, $x-$y_legend_hwidth, $this->margin_top+$plot_sizes[1]+5, $x+$y_legend_hwidth, $this->margin_top+$plot_sizes[1]+5+$fheight, ALIGN_CENTER, VALIGN_MIDDLE, 0, $key, $this->get_color('font_color'));
			
			$i++;
		}
	}
	
	protected function draw_point($x, $y, $style, $color, $fillcolor=NULL)
	{
		switch ($style)
		{
			case 'circle':
				if ($fillcolor)
					imagefilledellipse($this->image, $x, $y, $this->point_width, $this->point_width, $fillcolor);	// draw point background
				imageellipse($this->image, $x, $y, $this->point_width, $this->point_width, $color);	// draw point
				break;
			case 'diamond':
				$radius = round($this->point_width/2);
				$points = array(
					$x-$radius,	$y,					// left
					$x				,	$y-$radius,	// bottom
					$x+$radius,	$y,					// right
					$x				,	$y+$radius		// top
				);
				
				if ($fillcolor)
					imagefilledpolygon($this->image, $points, count($points)/2, $fillcolor);	// draw point background
				
				for ($ei=0; $ei<2; $ei++)
				{
					$points[0] += $ei;
					$points[3] += $ei;
					$points[4] -= $ei;
					$points[7] -= $ei;
					imagepolygon($this->image, $points, count($points)/2, $color);	// draw point
				}
				break;
		}
		$radius = round($this->point_width/2);
		$this->add_occupied($x-$radius, $y-$radius, $x+$radius, $y+$radius);
	}

} 