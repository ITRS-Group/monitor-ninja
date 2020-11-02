<?php

Event::insert_event('ninja.menu.setup', 0, function () {

    $menu = Event::$data;

    $menu->set('Branding', null, 0, null, array('style' => 'padding-top: 2px'))
        ->get('Branding')->set_html_label(brand::get('', true, true))
        ->set('About', 'menu/about', 0, '', array('id' => 'open-about-button'))
        ->set('HTTP API', '/api/help', 3, '', array('target' => '_blank'));

    $menu->set('Monitor', null, 2, '', array('style' => 'padding-top: 8px'))->get('Monitor');

    $menu->set('Report', null, 3, '', array('style' => 'margin-top: 8px'));

    $menu->set('Manage', null, 4, '', array('style' => 'margin-top: 8px'));

    Event::$data = $menu;

});
