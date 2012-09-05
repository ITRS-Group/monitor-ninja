<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * A controller that is available without authorization.
 * The methods here should therefore be comletely "pure", and only access
 * their parameters.
 *
 * @TODO: still, we should check that the user comes from a sane place.
 */
class Public_Controller extends Controller {
	/**
	*	Create a piechart
	*/
	public function piechart($in_data=false, $path=null)
	{
		$this->auto_render = false;
		$data = i18n::unserialize( base64_decode($in_data) );
		charts::load('Pie');
		$graph = new PieChart(300, 200);
		$graph->set_data($data, 'pie');
		$graph->set_margins(30);

		$graph->draw();
		if (!is_null($path)) {
			# save rendered image to somewhere ($path)
			if (file_exists($path) && is_writable($path)) {
				$image = $graph->get_image();

				# create temp filename with 'pie' as prefix just to
				# be able to tell where they come from in case of problems
				$tmpname = tempnam($path, 'pie');

				# remove the created empty file - we really just want the filename
				unlink($tmpname);

				$tmpname .= '.png';
				file_put_contents($tmpname, $image);

				# return path to file
				return $tmpname;
			}
		} else {
			$graph->display();
		}
	}

	/**
	*	Create a barchart
	*/
	public function barchart($in_data=false, $path=null)
	{
		$this->auto_render = false;
		$data = i18n::unserialize( base64_decode($in_data) );
		charts::load('MultipleBar');
		$graph = new MultipleBarChart(800, 600);

		$barvalues = false;
		$barcolors = false;
		foreach ($data as $tmpkey => $tmpval) {
			$barvalues[$tmpkey] = array($tmpval[1], $tmpval[0]);
			$barcolors[] = false;
			$barcolors[] = $tmpval[2] ? Reports_Controller::$colors['red'] : Reports_Controller::$colors['green'];
		}

		$graph->add_bar_colors($barcolors);
		$graph->set_background_style(null);
		$graph->set_plot_bg_color('#fff');
		$graph->set_data($barvalues);
		$graph->set_margins(7, 20);
		$graph->set_approx_line_gap(50);
		$graph->set_legend_y(_('Percent (%)'));
		$graph->set_legend_x(_('Period'));

		$graph->draw();
		if (!is_null($path)) {
			# save rendered image to somewhere ($path)
			if (file_exists($path) && is_writable($path)) {
				$image = $graph->get_image();

				# create temp filename with 'pie' as prefix just to
				# be able to tell where they come from in case of problems
				$tmpname = tempnam($path, 'bar');

				# remove the created empty file - we really just want the filename
				unlink($tmpname);

				$tmpname .= '.png';
				file_put_contents($tmpname, $image);

				# return path to file
				return $tmpname;
			}
		} else {
			$graph->display();
		}
	}

	/**
	 * Display chart for $chart_key
	 *
	 * Warning: this is actually potentially secret, but we need it from localhost
	 * for reports, so we must manually check for source IP or username.
	 *
	 * @param string $chart_key
	 */
	public function line_point_chart($chart_key) {
		// No current user
		if (!Auth::instance()->get_user()) {
			$addr = ip2long($_SERVER['REMOTE_ADDR']);
			// And we don't come from ::1 or 127.0.0.0/8
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !($_SERVER['REMOTE_ADDR'] == '::1' || ($addr & (127 << 24)) !== (127 << 24))) {
				// So we won't do anything
				die("Invalid request");
			}
		}

		$trends_graph_model = new Trends_graph_Model();
		$trends_graph_model->display_chart($chart_key);
	}
}
