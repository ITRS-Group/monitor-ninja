<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $data array */
?>
<div id="export-page-banner">
    <div class="export-detail-banner <?php echo($data['class']);?>">
        <span><?php echo($data['title'])?></span>
        <a href='details' class='view-export-details'
           name="<?php echo($data['title'])?>">View details</a>
    </div>
</div>