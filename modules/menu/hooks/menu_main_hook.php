<?php

Event::add('system.post_controller_constructor', function () {

  if (PHP_SAPI !== 'cli') {
    $predata = Event::$data;

    $menu = new Menu_Model();
    $menu->set('Monitor', null, 1, 'icon-16 x16-monitoring', array('style' => 'padding-top: 8px'))->get('Monitor')
      ->set('Tactical Overview', 'tac', 0, 'icon-16 x16-tac')
      ->set('Network Outages', 'outages', 1, 'icon-16 x16-outages')
      ->set('NagVis', 'nagvis', null, 'icon-16 x16-nagvis');

    // This is a nested event
    Event::run('ninja.menu.setup', $menu);

    /* Kohana cries when attempting nested event, write back the data and name
    for post_controller__constructor */
    Event::$name = 'system.post_controller_constructor';
    Event::$data = $predata;
  }

});