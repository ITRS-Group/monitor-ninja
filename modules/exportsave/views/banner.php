<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $data array */
?>
<div id="export-page-banner" class="notify-notification-bar">
    <div class="alert <?php echo($data['class']);?>">
<?php
        if($data['status'] == 'pending' || $data['status'] == 'running') {
            echo('<img height="10" width="10" alt=""
                src="/ninja/application/media/images/rolling-1s-200px.gif" />');
        }
?>
        <span><?php echo($data['title'])?></span>
        <a href='details' class='view-export-details'
           name="<?php echo($data['title'])?>">View details</a>
    </div>
</div>