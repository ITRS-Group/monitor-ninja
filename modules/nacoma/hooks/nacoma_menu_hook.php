<?php

  Event::add('ninja.menu.setup', function () {

    $auth = Auth::instance();
    $menu = Event::$data;

    if (op5MayI::instance()->run('ninja.configuration:read') && Kohana::config('config.nacoma_path') !== false) {

      $menu->set('Manage', null, 4, 'icon-16 x16-configuration', array('style' => 'margin-top: 8px'));

      $menu->set('Manage.Configure', 'configuration/configure', 0, 'icon-16 x16-nacoma');
      $menu->set('Manage.View active config', 'config', 1, 'icon-16 x16-viewconfig');
      $menu->set('Manage.Backup/Restore', 'backup', 2, 'icon-16 x16-backup');

      $menu->set('Manage.Scheduling queue', 'extinfo/scheduling_queue', 4, 'icon-16 x16-schedulingqueue');

      $menu->set('Manage.Performance information', 'extinfo/performance', 5, 'icon-16 x16-info');
      $menu->set('Manage.Process information', 'extinfo/show_process_info', 6, 'icon-16 x16-info');

    }

  });