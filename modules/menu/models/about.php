<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * About model
 *
 * @author Tobias Sjöndin <tobias.sjondin@op5.com>
 * @version 1.0
 */
class About_Model {

  private $about_info = array();

  /**
   * Adds a label-value  pair to display in the info-box
   *
   * @param $label       What label to display
   * @param $data        What value for the label
   */
  public function set ($label, $data) {

    $this->about_info[$label] = $data;

  }

  /**
   * Returns all label-value pairs
   *
   * @return array
   */
  public function get_all () {

    return $this->about_info;

  }

}