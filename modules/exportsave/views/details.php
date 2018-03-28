<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $data array */

echo("<div class='export-detail-description'>" . $data['description'] . "</div>");

$step_counter = 1;
$numberOfSteps = count($data['all_steps']);
foreach($data['all_steps'] as $step => $value) {
    $current_step = ($step_counter == $data['active_icon_number'] ? true : false);
    if($value['state'] == 'rollback') {
        $numberOfSteps = $numberOfSteps - 1;
        if($value['progress'] < 1 && $data['rollback']) {
?>
        <p class="export-detail-text-in-progress export-detail-text-in-progress-top">
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
        continue;
    } else if($data['status'] == 'success') {
        $current_step = false;
        $extra_classes = 'export-state-all-success';
    } else if($data['status'] == 'fail') {
        $extra_classes = 'export-state-rollback';
        if($current_step) {
            $extra_classes .= ' current-export-state-rollback';
        }
        if(is_int($value['icon']) && !$current_step) {
            $extra_classes .= ' export-state-pending';
        }
    } else if($data['status'] == 'running' && $data['rollback']) {
        $extra_classes = 'export-state-rollback';
        if($current_step) {
            $extra_classes .= ' current-export-state-rollback';
        }
        if(is_int($value['icon']) && !$current_step) {
            $extra_classes .= ' export-state-pending';
        }
    } else {
        $extra_classes = ($current_step ? 'current-export-state' : '');
        if(is_int($value['icon']) && !$current_step) {
            $extra_classes = 'export-state-pending';
        }
    }
    if($data['status'] == 'running' && $data['rollback'] && $data['active_icon_number'] == $step_counter) {
        $icon = icon::get('cancel-circled');
    } else {
        $icon = $value['icon'];
    }
?>
    <div class="export-detail-bar export-<?php echo($value['class'])?>-details <?php echo($extra_classes) ?>">
        <div class="export-detail-icon"><?php echo($icon)?></div>
        <p class="export-detail-text"><?php echo($value['step_name'])?>
<?php
        if($current_step && $data['status'] !== 'fail' && !$data['rollback']) {
            if(!empty($value['details'])) {
                echo(" <span class='export-state-detail'>" . $value['details'] . "</span>");
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
        $step_counter++;
        if($step_counter != $numberOfSteps) {
?>
            <div class="vertical-timeline"></div>
<?php
        }
?>
    </div>
<?php
}
