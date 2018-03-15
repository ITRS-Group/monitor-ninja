<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $data array */

echo("<div class='export-detail-description'>" . $data['description'] . "</div>");

$step_counter = 1;
foreach($data['all_steps'] as $step => $value) {
    if($data['status'] == 'success') {
        $current_step = false;
        $extra_classes = 'export-state-all-success';
    } else if($data['status'] == 'fail') {
        $current_step = ($step_counter == $data['current_step'] ? true : false);
        $extra_classes = 'export-state-rollback';
        if($current_step) {
            $extra_classes .= ' current-export-state-rollback';
        }
        if(is_int($value['icon']) && !$current_step) {
            $extra_classes .= ' export-state-pending';
        }
    } else {
        $current_step = ($step_counter == $data['current_step'] ? true : false);
        $extra_classes = ($current_step ? 'current-export-state' : '');
        if(is_int($value['icon']) && !$current_step) {
            $extra_classes = 'export-state-pending';
        }
    }
?>
    <div class="export-detail-bar export-<?php echo($value['class'])?>-details <?php echo($extra_classes) ?>">
        <div class="export-detail-icon"><?php echo($value['icon'])?></div>
        <p class="export-detail-text"><?php echo($value['step_name'])?>
<?php
        if($current_step && $data['status'] !== 'fail') {
            if(!empty($value['details'])) {
                echo(" <span class='export-state-detail'>(" . $value['details'] . ")</span>");
            }
?>
            </p>
        <p class="export-detail-text-in-progress">
            <img src="/ninja/application/media/images/rolling-1s-200px.gif"
                height="8" width="8" alt="" /> In progress
<?php
            if($value['progress'] > 0) {
                echo(" (" . ($value['progress'] * 100) . "%)");
            }
?>
        </p>
<?php
        }
        if($step_counter != count($data['all_steps'])) {
?>
            <div class="vertical-timeline"></div>
<?php
        }
        $step_counter++;
?>
    </div>
<?php
}
