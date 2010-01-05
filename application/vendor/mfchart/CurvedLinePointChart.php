<?php

class CurvedLinePointChart extends LinePointChart {

	protected $shaded = TRUE;
	

	public function __construct($width=NULL, $height=NULL)
	{
		parent::__construct($width, $height);
		
		$this->type = 'curved';
	}
	
	public function set_shaded($value)
	{
		$this->shaded = $value;
	}
	
	
	protected function draw_values($zeroline, $ratio)
	{
		$plot_sizes = $this->get_plot_area();
 		$font_point_legend = $this->font_size < 1 ? $this->font_size : $this->font_size-1;
 		
		$total_groups = count($this->values);	// count of all groups of points
 		$gap = ($plot_sizes[0] - $total_groups) / ($total_groups+1);	// gap between points
		
		// -- compute positions of points of lines
		
		$lines = array();
		
		$i=0;
		foreach ($this->values AS $key => $v)
		{
			$x = $this->margin_left + $gap + 1 + $i*($gap+1);	// 1 is considered as point width
			$y2 = $this->height - $this->margin_bottom - $zeroline;
			
			foreach ($v as $j => $value)
			{
				$y = $y2 - $value*$ratio;
				$lines[$j][] = array($x, $y, $value);
			}
			
			if ($this->vertical_point_lines)
				imageline($this->image, $x, $this->margin_top-1, $x, $this->height-$this->margin_bottom-1, IMG_COLOR_STYLED);
			
			$box_points = imagettfbbox($this->font_size, 0, $this->font, $key);
			$y_legend_hwidth = ($box_points[4]-$box_points[6]) / 2;
			$fheight = $box_points[3]-$box_points[5];
			
 			// x axis legend
 			utilities::imagestringbox($this->image, $this->font, $this->font_size, $x-$y_legend_hwidth, $this->margin_top+$plot_sizes[1]+5, $x+$y_legend_hwidth, $this->margin_top+$plot_sizes[1]+5+$fheight, ALIGN_CENTER, VALIGN_MIDDLE, 0, $key, $this->get_color('font_color'));
			
			$i++;
		}
		
		// -- draw lines
		
// 		$dark = imagecolorallocatealpha($this->image, 49, 49, 49, 95);
// 		$dark2 = imagecolorallocatealpha($this->image, 140, 140, 140, 95);
// 		$dark3 = imagecolorallocatealpha($this->image, 200, 200, 200, 95);
		
		foreach ($lines as $i => $line)
		{
			$color = utilities::hex2rgb($this->get_color('pen_color', $i, 0));
			
			if (count($line) < 3)	// for curved chart we need at least 3 points
			{	// classic
				for ($j=0; $j<count($line)-1; $j++)
					utilities::imagesmoothline($this->image, $line[$j][0], $line[$j][1], $line[$j+1][0], $line[$j+1][1], $color[0], $color[1], $color[2], $this->pen_width);
			}
			else
			{	// curved
				$z = $this->cubic_interpolation_z($line);
				
				$first_node = $line[0][0];
				$last_node = $line[count($line)-1][0];
				$previous = array($line[0][0], $line[0][1]);
				for ($x=$first_node; $x<=$last_node; $x++)
				{
					$y = $this->cubic_interpolation_s($x, $line, $z);
					if ($this->shaded)
					{	// draw shade
	// 					imageline($this->image, $previous[0], $previous[1]+$this->pen_width-1, $x, $y+$this->pen_width-1, $dark);
	// 					imageline($this->image, $previous[0], $previous[1]+$this->pen_width, $x, $y+$this->pen_width, $dark2);
	// 					imageline($this->image, $previous[0], $previous[1]+$this->pen_width+1, $x, $y+$this->pen_width+1, $dark3);
						utilities::imagesmoothalphaline($this->image, $previous[0], $previous[1]+$this->pen_width-1, $x, $y+$this->pen_width-1, 49, 49, 49, 95);
						utilities::imagesmoothalphaline($this->image, $previous[0], $previous[1]+$this->pen_width, $x, $y+$this->pen_width, 140, 140, 140, 95);
						utilities::imagesmoothalphaline($this->image, $previous[0], $previous[1]+$this->pen_width+1, $x, $y+$this->pen_width+1, 140, 140, 140, 95);
						utilities::imagesmoothalphaline($this->image, $previous[0], $previous[1]+$this->pen_width+2, $x, $y+$this->pen_width+2, 200, 200, 200, 95);
					}
					
	// 				imagesetthickness($this->image, $this->pen_width);
	// 				imageline($this->image, $previous[0], $previous[1], $x, $y, $this->get_color('pen_color', $i));
	// 				imagesetthickness($this->image, 1);
					
					utilities::imagesmoothline($this->image, $previous[0], $previous[1], $x, $y, $color[0], $color[1], $color[2], $this->pen_width);
					
					$previous = array($x, $y);
				}
			}
			
			foreach ($line as $point)
			{
				$this->draw_point($point[0], $point[1], $this->point_style, $this->get_color('point_color', $i), $this->get_color('point_fill_color', $i));
				
				$box_points = imagettfbbox($this->font_size, 0, $this->font, $value);
				$point_legend_hwidth = ($box_points[4]-$box_points[6]) / 2;
				$fheight = $box_points[3]-$box_points[5];
				
				// point legend
				if ($this->point_legend)
				{
					utilities::imagestringbox($this->image, $this->font, $font_point_legend, $point[0]-$point_legend_hwidth, $point[1]-$fheight-5-$this->point_width/2, $point[0]+$point_legend_hwidth, $point[1]-$this->point_width/2, ALIGN_CENTER, VALIGN_MIDDLE, 0, $point[2], $this->get_color('font_color3'));
					$this->add_occupied($point[0]-$point_legend_hwidth, $point[1]-$fheight-5-$this->point_width/2, $point[0]+$point_legend_hwidth, $point[1]-$this->point_width/2);
				}
			}
		}
	}
	
	function cubic_interpolation_z($points)
	{
		// find Zi
		
		// compute Hi and Bi
		$h = array();
		$b = array();
		for ($i=0; $i<count($points)-1; $i++)
		{
			$h[$i] = $points[$i+1][0]-$points[$i][0];
			$b[$i] = ($points[$i+1][1]-$points[$i][1])/$h[$i];
		}
		
		// gaussian elimination
		$u = array();
		$v = array();
		$u[1] = 2*($h[0]+$h[1]);
		$v[1] = 6*($b[1]-$b[0]);
		for ($i=2; $i<count($points)-1; $i++)
		{
			$u[$i] = 2*($h[$i-1]+$h[$i]) - pow($h[$i-1], 2)/$u[$i-1];
			$v[$i] = 6*($b[$i]-$b[$i-1]) - $h[$i-1]*$v[$i-1]/$u[$i-1];
		}
		
		// back substitution
		$z = array();
		$z[count($points)-1] = 0;
		for ($i=count($points)-2; $i>0; $i--)
		{
			$z[$i] = ($v[$i] - $h[$i]*$z[$i+1]) / $u[$i];
		}
		$z[0] = 0;

		return $z;
	}
	
	function cubic_interpolation_s($x, $points, $z)
	{
		// find Si interval
		for ($i=0; $i<count($points)-1; $i++)
			if ($x <= $points[$i+1][0])
				break;
		
		$h = $points[$i+1][0]-$points[$i][0];
		
		// compute a, b, c, d
		$a = $points[$i][1];
		$b = - ($h/6)*$z[$i+1] - ($h/3)*$z[$i] + ($points[$i+1][1]-$points[$i][1])/$h;
		$c = $z[$i]/2;
		$d = ($z[$i+1]-$z[$i])/(6*$h);
		
		$e = $x-$points[$i][0];
		
		return $a + $e*( $b+$e*( $c+$e*$d ) );
	}

} 