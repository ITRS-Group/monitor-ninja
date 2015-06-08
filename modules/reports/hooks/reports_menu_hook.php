<?php

  function get_saved_of_type ($type) {

    $filters = new SavedReportPool_Model();
    $set = $filters->all()->reduce_by('type', $type, '=');
    return $set->it(false, array());

  }

  Event::add('ninja.menu.setup', function () {

    $menu = Event::$data;
    $max_filters = 6;

    $menu->set('Report', null, 2, 'icon-16 x16-reporting', array('style' => 'margin-top: 8px'));

    $menu->set('Report.Availability', null, 0, 'icon-16 x16-availability');
    $menu->set('Report.Availability.Setup Availability Report', 'avail/index', 0, 'icon-16 x16-availability');

    $it = get_saved_of_type('avail');
    $count = 0;
    foreach ($it as $report) {

      $count++;
      if ($count > $max_filters) {
        $menu->set(
          'Report.Availability.View all Availability reports',
          listview::querylink('[saved_reports] type="avail"'), null, sprintf('icon-16 x16-%s', 'filter')
        );
        break;
      }

      $menu->set(
        'Report.Availability.' . $report->get_report_name(),
        'avail/generate?report_id' . $report->get_id(), null, 'icon-16 x16-availability'
      );

    }

    $menu->set('Report.SLA', null, 1, 'icon-16 x16-sla');
    $menu->set('Report.SLA.Setup SLA Report', 'sla/index', 1, 'icon-16 x16-sla');

    $it = get_saved_of_type('sla');
    $count = 0;

    foreach ($it as $report) {

      $count++;
      if ($count > $max_filters) {
        $menu->set(
          'Report.SLA.View all SLA reports',
          listview::querylink('[saved_reports] type="sla"'), null, sprintf('icon-16 x16-%s', 'filter')
        );
        break;
      }

      $menu->set(
        'Report.SLA.' . $report->get_report_name(),
        'sla/generate?report_id' . $report->get_id(), null, 'icon-16 x16-sla'
      );
    }

    $menu->set('Report.Histogram', null, 2, 'icon-16 x16-histogram');
    $menu->set('Report.Histogram.Setup Histogram', 'histogram/index', 2, 'icon-16 x16-histogram');

    $it = get_saved_of_type('histogram');
    $count = 0;

    foreach ($it as $report) {

      $count++;
      if ($count > $max_filters) {
        $menu->set(
          'Report.Histogram.View all Histogram Reports',
          listview::querylink('[saved_reports] type="histogram"'), null, sprintf('icon-16 x16-%s', 'filter')
        );
        break;
      }

      $menu->set(
        'Report.Histogram.' . $report->get_report_name(),
        'histogram/generate?report_id' . $report->get_id(), null, 'icon-16 x16-histogram'
      );
    }

    $menu->set('Report.Alert Summary', null, 3, 'icon-16 x16-alertsummary');
    $menu->set('Report.Alert Summary.Setup Alert Summary', 'summary', 0, 'icon-16 x16-alertsummary');

    $it = get_saved_of_type('summary');
    $count = 0;

    foreach ($it as $report) {

      $count++;
      if ($count > $max_filters) {
        $menu->set(
          'Report.Summary.View all Alert Summaries',
          listview::querylink('[saved_reports]  type="summary"'), null, sprintf('icon-16 x16-%s', 'filter')
        );
        break;
      }

      $menu->set(
        'Report.Alert summary.' . $report->get_report_name(),
        'summary/generate?report_id' . $report->get_id(), null, 'icon-16 x16-alertsummary'
      );
    }

    if (Kohana::config('config.pnp4nagios_path') !== false) {
      $menu->set('Report.Graphs', 'pnp?host=.pnp-internal&srv=runtime', 4, 'icon-16 x16-pnp');
    }

    $menu->set('Report.Saved reports', listview::querylink('[saved_reports] all'), 5, 'icon-16 x16-saved-reports');

    $menu->set('Report.Alert history', 'alert_history/generate', 6, 'icon-16 x16-alerthistory');
    $menu->set('Report.Schedule reports', 'schedule/show', 7, 'icon-16 x16-schedulereports');
    $menu->set('Report.Event log', 'showlog/showlog', 8, 'icon-16 x16-eventlog');

  });
