<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Menu-related (and explicit) controls
 */
class Menu_Controller extends Ninja_Controller {

  /**
   * Renders the about view
   */
  public function about () {

    $about = new About_Model();
    Event::run('ninja.version.info', $about);

    $this->template = new View('about');
    $this->template->about = $about;

  }

}