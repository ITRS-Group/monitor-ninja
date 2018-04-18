<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $data array */
?>
<div id="export-page-banner">
    <div class="export-detail-banner <?php echo($data['class']);?>">
        <span><?php echo($data['name'])?></span>
        <a href='details' class='view-export-details'
           name="<?php echo($data['name'])?>">View details</a>
    </div>
</div>