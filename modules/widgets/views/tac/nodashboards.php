<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<h1>No dashboards</h1>
<p>There are no dashboards available.</p>
<p>
    <a class="menuitem_dashboard_option" href=<?php
        echo LinkProvider::factory()->get_url('tac', 'new_dashboard_dialog');
    ?>>Click here to add dashboard!</a>
</p>