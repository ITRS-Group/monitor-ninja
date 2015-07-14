<?php

Event::add('ninja.menu.setup', function () {

  $menu = Event::$data;

  $menu->set('Branding', null, 0, null, array('style' => 'padding-top: 2px'))
    ->get('Branding')->set_label(brand::get())
    ->set('About', null, 0, 'icon-16 x16-op5', array('id' => 'open-about-button'))
    ->set('op5 Portal', '/', 0, 'icon-16 x16-op5', array('target' => '_blank'))
    ->set('op5 Manual', Kohana::config('menu.manual_url'), 1, 'icon-16 x16-manual', array('target' => '_blank'))
    ->set('op5 Support', 'http://www.op5.com/support', 2, 'icon-16 x16-support', array('target' => '_blank'))
    ->set('HTTP API', '/api/help', 3, 'icon-16 x16-op5', array('target' => '_blank'));

  Event::$data = $menu;

});

Event::add('system.post_controller_constructor', function () {

  $controller = Event::$data;

  $controller->template->js[] = 'modules/menu/media/js/about.js';
  $controller->template->css[] = 'modules/menu/media/css/about.css';

});

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