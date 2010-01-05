<?php

// only 3d for now

class PieChart extends Chart {

	protected $legend_precision = 1; // percentage round

	protected $spacing = 2; // spacing between slices


	public function __construct($width=NULL, $height=NULL)
	{
		parent::__construct($width, $height);

		$this->set_margins(10);

		$this->colors['colors'] = array(	// for drawing "value lines"
				array('#f8d01b', NULL, NULL),
				array('#f1b71e', NULL, NULL),
				array('#e38a23', NULL, NULL),
				array('#d65728', NULL, NULL),
				array('#ca1d2f', NULL, NULL),
				array('#c8004d', NULL, NULL),
				array('#db006f', NULL, NULL),
				array('#ef008f', NULL, NULL),
				array('#f7009a', NULL, NULL)
			);
		$this->colors['legend_color'] = 		array('#fefefe', NULL, NULL); // background legend color
	}

	public function set_legend_precision($value)
	{
		$this->legend_precision = (int) $value;
	}

	public function draw()
	{
		parent::draw();

		foreach ($this->values as $i => $value)
			if ($value < 0)
				$this->values[$i] = 0;

		$plot_sizes = $this->get_plot_area();	// size of the graph area
		$max_diameter = $plot_sizes[1];	// height of the plot area

		// get size of the percentage legend
		$t = '100'. ($this->legend_precision ? '.'. str_repeat('0', $this->legend_precision) : '');
		$box_points = imagettfbbox($this->font_size, 0, $this->font, $t);
		$legend_slice_width = $box_points[4]-$box_points[6];
		$legend_slice_height = $box_points[3]-$box_points[5];

		$width = $max_diameter-$this->spacing;
		$height = $max_diameter*0.6;
		$height_3d = $max_diameter*0.1;

		$this->legend = array_keys($this->values); // set legend

		// try to determine width of the legend box (exact width depends on relative font size which could be set when calling $this->draw_legend and margins)
		$legend_width = 0;
		foreach ($this->legend as $l)
		{
			$box_points = imagettfbbox($this->font_size, 0, $this->font, $l);
			$legend_width = $box_points[4]-$box_points[6];
			if ($legend_width < $width)
				$legend_width = $width;
		}
		$legend_width += 10; // some margins

		$margin_left = ($this->width-$legend_slice_width-$width-$legend_width) / 2;
		$center_x = $margin_left + $legend_slice_width + $width/2+20;
		$center_y = $this->margin_top + ($height+$height_3d+$legend_slice_height*2)/2;

		$ratio = array_sum($this->values)/360;

		if ($ratio != 0)	// so there is something to display
		{

			// steps for gradient in 3d
			$step = 2;
			$substep = 2;

			$start = 300;
			$slices = array();
			$i = 0;
			foreach ($this->values as $key => $value)
			{
				// make the color for 3d darker
				$color = utilities::hex2rgb($this->get_color('colors', $i, 0));
				$color[0] = $color[0]-40 < 0 ? 0 : $color[0]-40;
				$color[1] = $color[1]-40 < 0 ? 0 : $color[1]-40;
				$color[2] = $color[2]-40 < 0 ? 0 : $color[2]-40;

				$arc = $value/$ratio;
				$slices[] = array($start, $start+$arc, $color, $substep, round($arc/3.6, $this->legend_precision));
				$start += $arc;
				$i++;
			}

			// make the 3D effect
			for ($j=$height_3d; $j>0; $j--)
			{
				foreach ($slices as $i => $slice)
				{
					if (round($slice[0]) == round ($slice[1]))
						continue;

					$center_x_spaced = $center_x;
					$center_y_spaced = $center_y;
					if ($this->spacing)
					{
						$arc_half = $slice[0] + ($slice[1]-$slice[0])/2;
						$cos = cos(utilities::deg2rad($arc_half));
						$sin = sin(utilities::deg2rad($arc_half));
						$center_x_spaced = $center_x_spaced + $cos*$this->spacing;
						$center_y_spaced = $center_y_spaced - $sin*$this->spacing;
					}

					$color = $slice[2];
					$color[] = 0;
					imageSmoothArc($this->image, $center_x_spaced, $center_y_spaced+$j, $width, $height, $color, utilities::deg2rad($slice[0]), utilities::deg2rad($slice[1]));

					// try to determine next color for gradient
					if ($j % $step == 0)
				  {
						if ($slice[2][0] != 255)
							$slices[$i][2][0] = ($slice[2][0]+$slice[3] > 255) ? 255 : $slice[2][0]+$slice[3];
						else
							$slices[$i][3] += 1;
						if ($slice[2][1] != 255)
							$slices[$i][2][1] = ($slice[2][1]+$slice[3] > 255) ? 255 : $slice[2][1]+$slice[3];
						else
							$slices[$i][3] += 1;
						if ($slice[2][2] != 255)
							$slices[$i][2][2] = ($slice[2][2]+$slice[3] > 255) ? 255 : $slice[2][2]+$slice[3];
						else
							$slices[$i][3] += 1;
	        }
				}
			}

			foreach ($slices as $i => $slice)
			{
				if (round($slice[0]) == round ($slice[1]))
					continue;

				$arc_half = ($slice[0] + ($slice[1]-$slice[0])/2) % 360;
				$cos = cos(utilities::deg2rad($arc_half));
				$sin = sin(utilities::deg2rad($arc_half));

				$center_x_spaced = $center_x;
				$center_y_spaced = $center_y;
				if ($this->spacing)
				{
					$center_x_spaced = $center_x_spaced + $cos*$this->spacing;
					$center_y_spaced = $center_y_spaced - $sin*$this->spacing;
				}

				// draw the top
				$color = utilities::hex2rgb($this->get_color('colors', $i, 0));
				$color[] = 0;

				imageSmoothArc($this->image, $center_x_spaced, $center_y_spaced, $width, $height, $color, utilities::deg2rad($slice[0]), utilities::deg2rad($slice[1]));


				$a = $width/2;
				$b = $height/2;

				// radius of an ellipse
				$r = $a*$b / sqrt(pow($b,2)*pow($cos,2) + pow($a,2)*pow($sin,2));
				$r += 15; // padding from the graph

				$legend_slice_x = $center_x_spaced + $cos*$r;
				$legend_slice_y = (($arc_half > 180 AND $arc_half < 360) ? $center_y_spaced+$height_3d : $center_y_spaced) - $sin*$r;

				$text = $slice[4].'%';

				$box_points = imagettfbbox($this->font_size, 0, $this->font, $text);
				$legend_slice_width = $box_points[4]-$box_points[6];
				$legend_slice_hheight = ($box_points[3]-$box_points[5]) / 2;

				if (($arc_half >= 0 AND $arc_half < 90) OR ($arc_half >= 270 AND $arc_half < 360))
				{
					$legend_slice_x1 = $legend_slice_x+$legend_slice_hheight;
					$legend_slice_y1 = $legend_slice_y-$legend_slice_hheight;
					$legend_slice_x2 = $legend_slice_x+$legend_slice_width+$legend_slice_hheight;
					$legend_slice_y2 = $legend_slice_y+$legend_slice_hheight+5;
				}
				elseif (($arc_half >= 90 AND $arc_half < 180) OR ($arc_half >= 180 AND $arc_half < 270))
				{
					$legend_slice_x1 = $legend_slice_x-$legend_slice_width-$legend_slice_hheight;
					$legend_slice_y1 = $legend_slice_y-$legend_slice_hheight;
					$legend_slice_x2 = $legend_slice_x-$legend_slice_hheight;
					$legend_slice_y2 = $legend_slice_y+$legend_slice_hheight+5;
				}

				# print percent value
				utilities::imagestringbox($this->image, $this->font, $this->font_size, $legend_slice_x1, $legend_slice_y1, $legend_slice_x2, $legend_slice_y2, ALIGN_CENTER, VALIGN_MIDDLE, 0, $text, $this->get_color('font_color'));
			}
		}

		# print legend
		# $this->draw_legend('colors', 0, $center_x+$width/2-20+$legend_slice_width*3, $this->height/2-$height/2);
		$this->draw_legend('colors', 0, $center_x+$width/2-70+$legend_slice_width*3, $this->height/2-$height/2+85);

	}

}