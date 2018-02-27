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
                    'progress' => 0,
                    'details' => 'writing file.cfg'
                )
            ),
            'export_type' => 'user_export');

        $data['description'] = '';
        if($data['status'] == 'pending' || $data['status'] == 'running') {
            $data['title'] = 'Saving...';
            $data['class'] = 'wait';
        } else {
            $data['title'] = ucfirst($data['status']);
            $data['class'] = $data['status'];
        }

        $data['all_steps'] = $this->getAllStepInfo($data['status_details']);

        return $data;
    }

    public function getAllStepInfo($existing_details) {
        $step_list = array('backup', 'config_generation', 'verification', 'commit', 'rollback');

        $all_steps = array();
        foreach($step_list as $step) {
            foreach($existing_details as $details) {
                if($step == $details['state']) {
                    $all_steps[$step] = $details;
                    $all_steps[$step]['class'] = ($details['progress'] == 1 ? 'success' : '');
                    $all_steps[$step]['icon'] = ($details['progress'] == 1 ? icon::get('state-ok') : '<img src="/monitor/application/media/images/loading_small.gif" title="Loading..." />');
                }
            }
            $all_steps[$step]['step_name'] = $this->getStepName($step);
            $all_steps[$step]['progress'] = (empty($all_steps[$step]['progress']) ? '0' : $all_steps[$step]['progress']);
            $all_steps[$step]['class'] = (empty($all_steps[$step]['class']) ? '' : $all_steps[$step]['class']);
            $all_steps[$step]['icon'] = (empty($all_steps[$step]['icon']) ? '&nbsp;' : $all_steps[$step]['icon']);
        }
        return $all_steps;
    }


    public function getStepName($step) {
        switch($step) {
            case 'config_generation':
                $step_name = "Adding configuration";
                break;
            default:
                $step_name = str_replace("_", " ", ucfirst($step));
                break;
        }
        return $step_name;
    }
}