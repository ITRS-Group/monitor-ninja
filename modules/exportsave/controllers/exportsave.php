<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Controls for export/save status dialog
 */
class Exportsave_Controller extends Ninja_Controller {

    /**
    * Renders the view for export save, with banner, visible to everybody,
    * and css
    */
    public function banner () {
        $data = $this->get_details();
        $this->template->content = new View('banner', array( 'data' => $data ));
        $this->template->css[] = $this->add_path('../media/css/exportsave.css');
    }

    /**
     * Renders banner content.
     */
    public function banner_content() {
        $data = $this->get_details();
        $this->template = new View('banner', array( 'data' => $data ));
    }

    /**
     * Renders title bar for lightbox details.
     */
    public function current_status() {
        $data = $this->get_current_title();
        $this->template = new View('info', array( 'data' => $data));
    }

    /**
     * Renders the view for more details, with steps and more information.
     */
    public function details() {
        $data = $this->get_details();
        $this->template = new View('details', array('data' => $data));
    }

    /**
     * Right now, this function only returns hardcoded data.
     * Will be replaced with function that fetches data from database
     * @return $data array = array()
     */
    public function get_details() {
            $data = array(
                'id' => '123e4567-e89b-12d3-a456-426655440045',
                'creation_time' => 1982549018,
                'status' => 'running',
                'user' => 'monitor',
                'status_details' => array(
                    array(
                        'state' => 'backup',
                        'progress' => 1,
                        'details' => ''
                    ),
                    array(
                        'state' => 'config_generation',
                        'progress' => 0.42,
                        'details' => 'Writing host configuration files'
                    ),
                   array(
                        'state' => 'verification',
                        'progress' => 0,
                        'details' => ''
                    ),
                    array(
                        'state' => 'commit',
                        'progress' => 0,
                        'details' => ''
                    )
                ),
                'export_type' => 'user_export');

        $data['all_steps'] = $this->get_all_step_info($data['status_details'], $data['status']);
        $data['current_step_number'] = $this->get_current_step_number($data['all_steps']);
        $data['active_icon_number'] = $this->get_last_active_icon_number($data['all_steps']);
        $data['rollback'] = (is_int($data['all_steps']['rollback']['icon']) ? false : true);
        $data['rollback_progress'] = ($data['rollback'] ? ($data['all_steps']['rollback']['progress'] * 100) : '');
        $number_of_steps = count($data['all_steps']) - 1;

        switch($data['status']) {
            case 'pending':
            case 'running':
                if($data['rollback']) {
                    $data['banner'] = 'Changes could not be saved. Restoring to previous state (' . $data['rollback_progress'] . '%)';
                    $data['title'] = 'Changes could not be saved. Restoring...';
                    $data['description'] = 'Restoring to previous state.';
                    $data['class'] = 'error';
                } else {
                    $data['banner'] = 'Saving changes (step ' . $data['active_icon_number'] . ' of ' . $number_of_steps . ')';
                    $data['title'] = 'Saving changes...';
                    $data['description'] = 'Save progress (step ' . $data['active_icon_number'] . ' of ' . $number_of_steps . ')';
                    $data['class'] = 'info';
                }
                break;
            case 'success':
                $data['banner'] = 'Changes successfully saved';
                $data['title'] = ucfirst($data['status']);
                $data['description'] = 'Save completed';
                $data['class'] = 'success';
                break;
            case 'fail':
                $data['banner'] = ($data['rollback'] ? 'Changes could not be saved. Restored to previous state.' : 'Changes could not be saved.');
                $data['title'] = ($data['rollback'] ? 'Changes could not be saved. Restoring completed.' : 'Changes could not be saved.');
                $data['description'] = ($data['rollback'] ? 'Restored to previous state.' : '');
                $data['class'] = 'error';
                break;
            default:
                $data['banner'] = ucfirst($data['status']);
                $data['title'] = ucfirst($data['status']);
                $data['description'] = '';
                $data['class'] = 'info';
                break;
        }
        return $data;
    }

    /**
     * Fetches the current status title
     * @return $data['title'] string = ""
     */
    public function get_current_title() {
        $data = $this->get_details();
        return $data['title'];
    }

    /**
     * Creates an object with all the steps, even the ones that we haven't gone
     * through with yet.
     * This is where you can add or delete steps.
     * @param $existing_details array = array()
     * @param $status string = ""
     * @return $all_steps array = array()
     */
    public function get_all_step_info($existing_details, $status = "") {
        $step_list = array('backup', 'config_generation', 'verification', 'commit', 'rollback');

        $all_steps = array();
        $step_number = 1;
        foreach($step_list as $step) {
            foreach($existing_details as $details) {
                if($step == $details['state']) {
                    $all_steps[$step] = $details;
                    $all_steps[$step]['class'] = ($details['progress'] == 1 ? 'success' : '');
                    if($status == 'fail' && ($details['progress'] > 0 && $details['progress'] < 1)) {
                        $all_steps[$step]['icon'] = icon::get('cancel-circled');
                    } else if($status == 'running' && $details['state'] == 'rollback') {
                        $all_steps[$step]['icon'] = icon::get('cancel-circled');
                    } else {
                        $all_steps[$step]['icon'] = ($details['progress'] == 1 ? icon::get('state-ok') : $step_number);
                    }
                }
            }
            $all_steps[$step]['step_name'] = $this->get_step_name($step);
            $all_steps[$step]['progress'] = (empty($all_steps[$step]['progress']) ? '0' : $all_steps[$step]['progress']);
            $all_steps[$step]['class'] = (empty($all_steps[$step]['class']) ? 'pending' : $all_steps[$step]['class']);
            $all_steps[$step]['icon'] = (empty($all_steps[$step]['icon']) ? $step_number : $all_steps[$step]['icon']);
            $all_steps[$step]['state'] = $step;
            $step_number++;
        }
        return $all_steps;
    }

    /**
     * This is where we set step names from state. Used in lightbox details
     * @param $step string = ""
     * @return $step_name string = ""
     */
    public function get_step_name($step) {
        switch($step) {
            case 'backup':
                $step_name = "Backing up";
                break;
            case 'config_generation':
                $step_name = "Writing configuration";
                break;
            case 'verification':
                $step_name = "Verifying";
                break;
            case "commit":
                $step_name = "Committing";
                break;
            default:
                $step_name = str_replace("_", " ", ucfirst($step));
                break;
        }
        return $step_name;
    }

    /**
     * Fetches the step number where we currently are working.
     * @param $all_steps array = array()
     * @return $current_step int = 0
     */
    public function get_current_step_number($all_steps) {
        $step_number = 0;
        foreach($all_steps as $step => $details) {
            $step_number++;
            if($details['progress'] < 1) {
                $current_step = $step_number;
            }
        }
        return $current_step;
    }

    /**
     * Fetches the step number where we were working last time.
     * This function doesn't care if we are rollbacking. It fetches the last
     * active step, so that we can see were we were before rollback.
     * @param $all_steps array = array()
     * @return $step_number int = 0
     */
    public function get_last_active_icon_number($all_steps) {
        $icon_number = 0;
        foreach($all_steps as $step => $details) {
            $icon_number++;
            if($details['progress'] < 1) {
                return $icon_number;;
            }
        }
        return $icon_number;
    }
}