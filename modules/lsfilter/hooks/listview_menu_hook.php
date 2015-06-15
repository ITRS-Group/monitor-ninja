<?php

  function listview_menu_label($name) {
    return ucfirst(preg_replace("/\_/", " ", $name));
  }

  Event::add('ninja.menu.setup', function () {

    $menu = Event::$data;
    $mayi = op5MayI::instance();
    $max_filters = 6;

    $section = $menu->get('Monitor');
    $menu->set('Manage.Manage filters', listview::querylink('[saved_filters] all'), 3, 'icon-16 x16-eventlog');

    $tables = array(
      'hosts' => array('pool' => 'HostPool_Model', 'icon' => 'icon-16 x16-host'),
      'services' => array('pool' => 'ServicePool_Model', 'icon' => 'icon-16 x16-service'),
      'hostgroups' => array('pool' => 'HostgroupPool_Model', 'icon' => 'icon-16 x16-hostgroup'),
      'servicegroups' => array('pool' => 'ServicegroupPool_Model', 'icon' => 'icon-16 x16-servicegroup')
    );

    $index = 2;

    $saved = array();
    $set = SavedFilterPool_Model::all();

    foreach ($set->it(false, array('filter_name')) as $value) {
      $table = $value->get_filter_table();
      if (!isset($saved[$table])) $saved[$table] = array();
      $saved[$table][] = $value;
    }

    foreach ($tables as $table => $def) {

      $singular = preg_replace('/s$/', '', $table);
      $pool = $def['pool'];

      $resource = $pool::all()->mayi_resource();
      $key = listview_menu_label($table);

      if ($mayi->run($resource . ':read.list')) {

        $icon = preg_replace("/\_/", "-", $singular);
        $section->set($key, null, 2 + $index, sprintf('icon-16 x16-%s', $icon));

        $section->set(
          $key . '.All ' . $key,
          listview::querylink(sprintf('[%s] all', $table)),
          0, sprintf('icon-16 x16-%s', $icon)
        );

        $count = 0;
        if (isset($saved[$table])) {
          foreach ($saved[$table] as $object) {

            $count++;

            if ($count > $max_filters) {
              $section->set(
                $key . '.All filters for ' . preg_replace('/\./', '&period;', $table),
                listview::querylink(sprintf('[saved_filters] filter_table = "%s"', $table)),
                $index, sprintf('icon-16 x16-%s', 'filter')
              );
              break;
            }

            $section->set(
              $key . '.' . $object->get_filter_name(),
              listview::querylink($object->get_filter()),
              $index, sprintf('icon-16 x16-%s', $icon)
            );

          }
        }

        $index++;

      }

    }

    $section->set('Downtimes', null, ($index++) + 2, 'icon-16 x16-downtime');
    $section->set('Downtimes.All Downtimes', listview::querylink('[downtimes] all'), 0, 'icon-16 x16-downtime');
    $section->set('Downtimes.Recurring Downtimes', listview::querylink('[recurring_downtimes] all'), 1, 'icon-16 x16-recurring-downtime');

    $menu->set('Report.Notifications', listview::querylink('[notifications] all'), null, 'icon-16 x16-notification');

  });
