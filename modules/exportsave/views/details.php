<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $data array */

echo($data['description']);
?>

<div class='export-detail-bar <?php echo($data["step_details"]["create"])?>-details'>
    <p class="export-detail-text">1. Creating relations</p>
    <p class="export-detail-icon">
        <?php
            if($data["step_details"]["create"] == 'state-ok' || $data["step_details"]["create"] == 'cancel-circled') {
                echo(icon::get($data["step_details"]["create"]));
            } else {
                echo('<img src="/monitor/application/media/images/loading_small.gif" title="Loading..." />');
            }
        ?>
    </p>
</div>
<div class='export-detail-bar <?php echo($data["step_details"]["write"])?>-details'>
    <p class="export-detail-text">2. Writing to file</p>
    <p class="export-detail-icon">
        <?php
            if($data["step_details"]["write"] == 'state-ok' || $data["step_details"]["write"] == 'cancel-circled') {
                echo(icon::get($data["step_details"]["write"]));
            } else {
                echo('<img src="/monitor/application/media/images/loading_small.gif" title="Loading..." />');
            }
        ?>
    </p>
</div>
<div class='export-detail-bar <?php echo($data["step_details"]["verify"])?>-details'>
    <p class="export-detail-text">3. Verifying configuration</p>
    <p class="export-detail-icon">
        <?php
            if($data["step_details"]["verify"] == 'state-ok' || $data["step_details"]["verify"] == 'cancel-circled') {
                echo(icon::get($data["step_details"]["verify"]));
            } else {
                echo('<img src="/monitor/application/media/images/loading_small.gif" title="Loading..." />');
            }
        ?>
    </p>
</div>
<div class='export-detail-bar <?php echo($data["step_details"]["restart"])?>-details'>
    <p class="export-detail-text">4. Restarting</p>
    <p class="export-detail-icon">
        <?php
            if($data["step_details"]["restart"] == 'state-ok' || $data["step_details"]["restart"] == 'cancel-circled') {
                echo(icon::get($data["step_details"]["restart"]));
            } else {
                echo('<img src="/monitor/application/media/images/loading_small.gif" title="Loading..." />');
            }
        ?>
    </p>
</div>