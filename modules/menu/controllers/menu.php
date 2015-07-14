<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Menu-related (and explicit) controls
 */
class Menu_Controller extends Ninja_Controller {

  /**
   * Renders the about view
   */
  public function about () {

    $this->auto_render = false;

    $status = Current_status_Model::instance()->program_status();
    $release = @file_get_contents('/etc/op5-monitor-release');

    if ($release) {
      $release = preg_replace('/VERSION=/', '', $release);
    } else {
      $release = "Unknown";
    }

    $this->template = $this->add_view('about');
    $this->template->version = (object) array(
      "product" => $status->program_version . " (" . trim($release) . ")",
      "livestatus" => $status->livestatus_version,
    );

    $this->template->render(TRUE);

  }

}