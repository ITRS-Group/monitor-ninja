<?php

  function get_saved_of_type ($type) {

    $filters = new SavedReportPool_Model();
    $set = $filters->all()->reduce_by('type', $type, '=');
    try {
        return $set->it(false, array());
    } catch (ORMDriverException $e) {
		op5log::instance('ninja')->log('error', "Failed to get saved reports: " . $e->getMessage());
        return new ArrayIterator(array());
    }

  }

  function add_report_menu_type ($menu, $order, $type, $label, $index, $link, $icon) {

    $it = get_saved_of_type($type);
    $max_filters = 6;

    $menu->set(sprintf('Report.%s', $label), null, $order, $icon);
    $menu->set(sprintf('Report.%s.Create %s Report', $label, $label), $index, 0, $icon);

    $count = 0;
    foreach ($it as $report) {

      $count++;
      if ($count > $max_filters) {
        $ns = sprintf('Report.%s.View all %s reports', $label, $label);
        $link = listview::querylink(sprintf('[saved_reports] type="%s"', $type));
        $menu->set($ns, $link, null, 'icon-16 x16-filter');
        break;
      }

      $ns = sprintf('Report.%s.', $label) . preg_replace('/\./', '&period;', $report->get_report_name());
      $menu->set($ns, $link . '?report_id=' . $report->get_id(), null, $icon);

    }
  }

  Event::add('ninja.menu.setup', function () {

    $menu = Event::$data;
    $menu->set('Report', null, 2, 'icon-16 x16-reporting', array('style' => 'margin-top: 8px'));

    add_report_menu_type($menu, 0, 'avail', 'Availability', 'avail/index', 'avail/generate', 'icon-16 x16-availability');
    add_report_menu_type($menu, 1, 'sla', 'SLA', 'sla/index', 'sla/generate', 'icon-16 x16-sla');
    add_report_menu_type($menu, 2, 'histogram', 'Histogram', 'histogram/index', 'histogram/generate', 'icon-16 x16-histogram');
    add_report_menu_type($menu, 3, 'summary', 'Summary', 'summary/index', 'summary/generate', 'icon-16 x16-alertsummary');

    if (Kohana::config('config.pnp4nagios_path') !== false) {
      $menu->set('Report.Graphs', 'pnp?host=.pnp-internal&srv=runtime', 4, 'icon-16 x16-pnp');
    }

    $menu->set('Report.Saved reports', listview::querylink('[saved_reports] all'), 5, 'icon-16 x16-saved-reports');

    $menu->set('Report.Alert history', 'alert_history/generate', 6, 'icon-16 x16-alerthistory');
    $menu->set('Report.Schedule reports', 'schedule/show', 7, 'icon-16 x16-schedulereports');
    $menu->set('Report.Event log', 'showlog/showlog', 8, 'icon-16 x16-eventlog');

  });
