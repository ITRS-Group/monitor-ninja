<?php

Event::add('ninja.logged_in', function () {

	$user = op5auth::instance()->get_user();

    if (!$user->has_logged_in()) {

        $login_time = new Setting_Model();
        $login_time->set_username($user->get_username());
        $login_time->set_type('login_time');
        $login_time->set_setting(time());
        $login_time->save();

        Event::run('ninja.first_login');

    }

    $login_time = SettingPool_Model::all()
        ->reduce_by('username', $user->get_username(), '=')
        ->reduce_by('type', 'login_time', '=')
        ->one();

    $login_time->set_setting(time());
    $login_time->save();

});

