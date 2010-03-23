<?php

class MultipleBarChart extends BarChart {

	protected $bar_width = 30;

	protected $bar_gap = -20;	// gap between the bars in a group

	public $bar_colors = false;

	public function __construct($width=NULL, $height=NULL)
	{
		parent::__construct($width, $height);

		$this->type = 'multiple';

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

	public function set_bar_gap($val=0)
	{
		$this->bar_gap = $val;
	}


	public function add_bar_colors($values=false)
	{
		if (empty($values))
			return false;

		/*
		foreach ($values as $val) {
			#$rgb = utilities::hex2rgb($val);
			$this->bar_colors[] = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);
		}
		*/
		$this->bar_colors = $values;
	}

	protected function draw_values($zeroline, $ratio)
	{
		#echo Kohana::debug($this->bar_colors);
		#die();
		$plot_sizes = $this->get_plot_area();
 		$font_bar_legend = $this->font_size < 1 ? $this->font_size : $this->font_size-1;

		reset($this->values);
		$total_bars_groups = count($this->values);
		$bars_per_group = count(current($this->values));	// count of all bars
		$gap = ($plot_sizes[0] - $total_bars_groups*$bars_per_group*$this->bar_width - $total_bars_groups*($bars_per_group-1)*$this->bar_gap) / ($total_bars_groups+1);	// gap between bars -- 5 is gap between group bars


		$i=0;
		$a=0;
		foreach ($this->values AS $key => $value)
		{
			$x1base = $this->margin_left + $gap + $i*($gap + (count($value)*($this->bar_width+$this->bar_gap)-$this->bar_gap) );

			$fheight = $this->font_size; // default - recomputed later in the next cycle

			$j = 0;
			foreach ($value as $v)
			{
				$x1 = $x1base + $j*($this->bar_width+$this->bar_gap);
				$x2 = $x1 + $this->bar_width;
				$y2 = $this->height - $this->margin_bottom - $zeroline;
				$y1 = $y2 - $v*$ratio;

				if ($this->vertical_bar_lines)
					imageline($this->image, $x1+$this->bar_width/2, $this->margin_top-1, $x1+$this->bar_width/2, $this->height-$this->margin_bottom-1, IMG_COLOR_STYLED);

				$col = $this->bar_colors[$a];
				if (!$col) {
					# sla color
					$this->bar_legend = false;
					$rgb = utilities::hex2rgb(Reports_Controller::$colors['lightblue']);
					$col = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);
				} else {
					$this->bar_legend = true;
					$rgb = utilities::hex2rgb($col);
					$col = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);
				}

				imagefilledrectangle($this->image, $x1, $y1, $x2, $y2, $col); // draw bar
				$this->add_occupied($x1, $y1, $x2, $y2);

				if ($this->get_color('bar_border_color'))
				{	// draw bar border
					imageline($this->image, $x1, $y1, $x1, $y2, $this->get_color('bar_border_color', $j)); // left
					imageline($this->image, $x2, $y1, $x2, $y2, $this->get_color('bar_border_color', $j)); // right
					imageline($this->image, $x1, $y1, $x2, $y1, $this->get_color('bar_border_color', $j)); // top
				}

				if ($v != 0)
					imagefilledrectangle($this->image, $x2, $y1+($v > 0 ? 1 : -1), $x2+1, $y2, $this->get_color('shade_color')); // draw shade of bar

				$box_points = imagettfbbox($this->font_size, 0, $this->font, $v);
				$bar_middle = $x1 + $this->bar_width/2;
				$bar_legend_hwidth = ($box_points[4]-$box_points[6]) / 2;
				$fheight = $box_points[3]-$box_points[5];

				// bar legend
				if ($this->bar_legend)
				{
					if ($v >= 0)
					{
						#utilities::imagestringbox($this->image, $this->font, $font_bar_legend, $bar_middle-$bar_legend_hwidth, $y1-$fheight-5, $bar_middle+$bar_legend_hwidth, $y1, ALIGN_CENTER, VALIGN_MIDDLE, 0, Reports_Controller::_format_report_value($v), $this->get_color('font_color3'));

						#utilities::imagestringbox($this->image, $this->font, $font_bar_legend, $bar_middle-$bar_legend_hwidth, 65, $bar_middle+$bar_legend_hwidth, 995, ALIGN_CENTER, VALIGN_MIDDLE, 0, Reports_Controller::_format_report_value($v), $this->get_color('font_color3'));
						#utilities::imagestringbox($this->image, $this->font, $font_bar_legend, $bar_middle-$bar_legend_hwidth, -150, $bar_middle+$bar_legend_hwidth, 700, ALIGN_LEFT, VALIGN_MIDDLE, 0, Reports_Controller::_format_report_value($v), $this->get_color('font_color3'));
						utilities::imagestringbox($this->image, $this->font, $font_bar_legend, $bar_middle-$bar_legend_hwidth, $y1-$fheight-5, $bar_middle+$bar_legend_hwidth, $y1, ALIGN_LEFT, VALIGN_MIDDLE, 0, reports::format_report_value($v), $this->get_color('font_color3'));
						#$this->add_occupied($bar_middle-$bar_legend_hwidth, $y1-$fheight-5, $bar_middle+$bar_legend_hwidth, $y1);
						$this->add_occupied($bar_middle-$bar_legend_hwidth, $y1-$fheight-5, $bar_middle+$bar_legend_hwidth, $y1);
					}
					else
					{
						utilities::imagestringbox($this->image, $this->font, $font_bar_legend, $bar_middle-$bar_legend_hwidth, $y1+5, $bar_middle+$bar_legend_hwidth, $y1+$fheight+5, ALIGN_CENTER, VALIGN_MIDDLE, 0, $v, $this->get_color('font_color3'));
						$this->add_occupied($bar_middle-$bar_legend_hwidth, $y1+5, $bar_middle+$bar_legend_hwidth, $y1+$fheight+5);
					}
				}

				$j++;
				$a++;
			}

			$box_points = imagettfbbox($this->font_size, 0, $this->font, $key);
			$y_legend_hwidth = ($box_points[4]-$box_points[6]) / 2;
			$group_bar_middle = $x1base + (count($value)*($this->bar_width+$this->bar_gap)-$this->bar_gap)/2;

 			// x axis legend
 			utilities::imagestringbox($this->image, $this->font, $this->font_size, $group_bar_middle-$y_legend_hwidth, $this->margin_top+$plot_sizes[1]+5, $group_bar_middle+$y_legend_hwidth, $this->margin_top+$plot_sizes[1]+5+$fheight, ALIGN_CENTER, VALIGN_MIDDLE, 0, $key, $this->get_color('font_color'));

			$i++;
		}

		#******************************************************************
		#	Draw SLA graph legend to explain bar colors
		#******************************************************************

		# colors
		$rgb = utilities::hex2rgb(Reports_Controller::$colors['lightblue']);
		$color = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);

		$rgb_red = utilities::hex2rgb(Reports_Controller::$colors['red']);
		$red_color = imagecolorallocate($this->image, $rgb_red[0], $rgb_red[1], $rgb_red[2]);

		$rgb_green = utilities::hex2rgb(Reports_Controller::$colors['green']);
		$green_color = imagecolorallocate($this->image, $rgb_green[0], $rgb_green[1], $rgb_green[2]);

		# black col
		$rgb = utilities::hex2rgb('#000000');
		$col = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);

		# legend boxes - 2 for each legend with one black box being 1 pixel larger to create a border
		imagefilledrectangle($this->image, 34, $this->height-30, 55, $this->height-45, $col); // draw sla compliance border
		imagefilledrectangle($this->image, 35, $this->height-31, 54, $this->height-44, $color); // draw sla compliance color legend

		imagefilledrectangle($this->image, 34, $this->height-10, 55, $this->height-25, $col); // draw sla breach border
		imagefilledrectangle($this->image, 35, $this->height-11, 54, $this->height-24, $red_color); // draw sla breach color legend

		imagefilledrectangle($this->image, 184, $this->height-10, 205, $this->height-25, $col); // draw fulfilled sla border
		imagefilledrectangle($this->image, 185, $this->height-11, 204, $this->height-24, $green_color); // draw fulfilled sla color legend

		# text color
		$rgb = utilities::hex2rgb('#000000');
		$col = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);
		$translate = zend::instance('Registry')->get('Zend_Translate');

		# legend strings:

		# sla compliance string
		utilities::imagestringbox($this->image, $this->font, $this->font_size, 255, $this->height-70, -50, $this->height, ALIGN_CENTER, VALIGN_MIDDLE, 0, $translate->_('SLA Compliance'), $col);

		# breached sla string
		utilities::imagestringbox($this->image, $this->font, $this->font_size, 255, $this->height-32, -60, $this->height, ALIGN_CENTER, VALIGN_MIDDLE, 0, $translate->_('Breached SLA'), $col);

		# Fulfilled sla string
		utilities::imagestringbox($this->image, $this->font, $this->font_size, 545, $this->height-32, -60, $this->height, ALIGN_CENTER, VALIGN_MIDDLE, 0, $translate->_('Fulfilled SLA'), $col);
	}
}