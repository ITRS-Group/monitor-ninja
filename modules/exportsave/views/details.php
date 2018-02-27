<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $data array */

echo($data['description']);

foreach($data['all_steps'] as $step => $value) {
?>
    <div class="export-detail-bar export-<?php echo($value['class'])?>-details">
        <p class="export-detail-text"><?php echo($value['step_name'])?></p>
        <p class="export-detail-icon"><?php echo($value['icon'])?></p>
    </div>
<?php
}
