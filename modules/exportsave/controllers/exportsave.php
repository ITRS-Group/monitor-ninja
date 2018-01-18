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
        $data = $this->getdetails();
        $this->template->content = new View('banner', array( 'data' => $data ));
        $this->template->css[] = $this->add_path('../media/css/exportsave.css');
    }

    /**
     * Renders banner content.
     */
    public function bannercontent() {
        $data = $this->getdetails();
        $this->template = new View('banner', array( 'data' => $data ));
    }

    /**
     * Renders the view for more details, with steps and more information.
     */
    public function details() {
        $data = $this->getdetails();
        $this->template = new View('details', array('data' => $data));
    }

    /**
     * Right now, this function only returns hardcoded data.
     * Will be replaced with function that fetches data from database
     * @return $data array = array()
     */
    public function getdetails() {
        $data = array('name' => 'Saving...',
            'description' => '',
            'creation_time' => 1982549018,
            'status' => 'waiting',
            'user' => 'monitor',
            'export_type' => 'user_export',
            'step_details' => array('create' => 'state-ok',
                                    'read' => 'state-ok',
                                    'write' => 'state-ok',
                                    'verify' => 'state-ok',
                                    'restart' => 'wait'),
            'icon' => 'load',
            'class' => 'wait');

        return $data;
    }
}