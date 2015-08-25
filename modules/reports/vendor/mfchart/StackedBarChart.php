<?php

class StackedBarChart extends BarChart {
	
	public function __construct($width=NULL, $height=NULL)
	{
		parent::__construct($width, $height);
		
		$this->type = 'stacked';
		
		$this->colors['bar_color'] = array(
				array('#e0d62e', NULL, NULL),
				array('#bdb51c', NULL, NULL),
				array('#9f9917', NULL, NULL),
				array('#807a13', NULL, NULL),
				array('#68640f', NULL, NULL),
				array('#e6de51', NULL, NULL),
				array('#eee988', NULL, NULL)
			);
		$this->colors['bar_border_color'] = array(
				array('#747014', 25, 	 NULL),
				array('#9d971c', 25, 	 NULL)
			);
	}
	
	
	protected function draw_values($zeroline, $ratio)
	{
		$plot_sizes = $this->get_plot_area();
 		$font_bar_legend = $this->font_size < 1 ? $this->font_size : $this->font_size-1;
 		
		$total_bars = count($this->values);	// count of all bars
 		$gap = ($plot_sizes[0] - $total_bars*$this->bar_width ) / ($total_bars+1);	// gap between bars
		
 		
		$i=0;
		foreach ($this->values AS $key => $value)
		{
			$x1 = $this->margin_left + $gap + $i*($gap+$this->bar_width);
			$x2 = $x1 + $this->bar_width;
			$y2 = $this->height - $this->margin_bottom - $zeroline;
			$y1 = array();
			foreach ($value as $v)
				$y1[] = $y2 - $v*$ratio;
			
			if ($this->vertical_bar_lines)
				imageline($this->image, $x1+$this->bar_width/2, $this->margin_top-1, $x1+$this->bar_width/2, $this->height-$this->margin_bottom-1, IMG_COLOR_STYLED);
			
			$sum = array_sum($value);	// sum of all values in a stack bar
			$bar_border = $y2 - $sum*$ratio;	// border of the sum in pixels from the top of the graph
			
			$j = 0;
			$base = 0;
			foreach ($y1 as $v)
			{
				$y1i = $v-$base;
				$y2i = $y2-$base;
				
				$to_draw = array($y1i, $y2i);
				$borders = array($bar_border, $y2);
				sort($to_draw);
				sort($borders);
				if ($to_draw[0] > $borders[1] OR $to_draw[1] < $borders[0])
					continue;
				if ($to_draw[1] > $borders[1])
					$to_draw[1] = $borders[1];
				if ($to_draw[0] < $borders[0])
					$to_draw[0] = $borders[0];
				
				imagefilledrectangle($this->image, $x1, $to_draw[0], $x2, $to_draw[1], $this->get_color('bar_color', $j)); // draw bar
				
				if ($this->get_color('bar_border_color'))
				{	// draw bar border
					imageline($this->image, $x1, $to_draw[0], $x1, $to_draw[1], $this->get_color('bar_border_color', $j)); // left
					if ($to_draw[1] > $y2)
						imageline($this->image, $x1, $to_draw[1], $x2, $to_draw[1], $this->get_color('bar_border_color', $j)); // bottom
					else
						imageline($this->image, $x1, $to_draw[0], $x2, $to_draw[0], $this->get_color('bar_border_color', $j)); // top
					imageline($this->image, $x2, $to_draw[0], $x2, $to_draw[1], $this->get_color('bar_border_color', $j)); // right
				}
				
				$base += $y2-$v + 1;
				$j++;
			}
			
			if ($sum != 0)
 				imagefilledrectangle($this->image, $x2, $bar_border+($sum > 0 ? 1 : -1), $x2+1, $y2, $this->get_color('shade_color')); // draw shade of bar
			
			$this->add_occupied($x1, $bar_border, $x2, $y2);
			
			$box_points = imagettfbbox($this->font_size, 0, $this->font, $sum);
			$bar_middle = $x1 + $this->bar_width/2;
			$bar_legend_hwidth = ($box_points[4]-$box_points[6]) / 2;
			$fheight = $box_points[3]-$box_points[5];
			
			// bar legend
			if ($this->bar_legend)
			{
				if ($sum >= 0)
				{
					utilities::imagestringbox($this->image, $this->font, $font_bar_legend, $bar_middle-$bar_legend_hwidth, $bar_border-$fheight-5, $bar_middle+$bar_legend_hwidth, $bar_border, ALIGN_CENTER, VALIGN_MIDDLE, 0, $sum, $this->get_color('font_color3'));
					$this->add_occupied($bar_middle-$bar_legend_hwidth, $bar_border-$fheight-5, $bar_middle+$bar_legend_hwidth, $bar_border);
				}
				else
				{
					utilities::imagestringbox($this->image, $this->font, $font_bar_legend, $bar_middle-$bar_legend_hwidth, $bar_border+5, $bar_middle+$bar_legend_hwidth, $bar_border+$fheight+5, ALIGN_CENTER, VALIGN_MIDDLE, 0, $sum, $this->get_color('font_color3'));
					$this->add_occupied($bar_middle-$bar_legend_hwidth, $bar_border+5, $bar_middle+$bar_legend_hwidth, $bar_border+$fheight+5);
				}
			}
			
			
			$box_points = imagettfbbox($this->font_size, 0, $this->font, $key);
			$y_legend_hwidth = ($box_points[4]-$box_points[6]) / 2;
			
 			// x axis legend
 			utilities::imagestringbox($this->image, $this->font, $this->font_size, $bar_middle-$y_legend_hwidth, $this->margin_top+$plot_sizes[1]+5, $bar_middle+$y_legend_hwidth, $this->margin_top+$plot_sizes[1]+5+$fheight, ALIGN_CENTER, VALIGN_MIDDLE, 0, $key, $this->get_color('font_color'));
			
			$i++;
		}
	}

} 