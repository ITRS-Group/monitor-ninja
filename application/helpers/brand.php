<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Helper to get the branding HTML
 */
class brand {

  /**
   * Default image to use when unlicensed or license
   * without an icon
   */
  const DEFAULT_IMAGE = 'default.png';

  /**
   * Generates brading based based on license, if any, and
   * if the image exists, otherwise return generic branding
   * blob
   *
   * @return string A HTML blob to use in branding
   */
  public static function get($host = "") {

    // Default branding, ninja in community, op5 banana otherwise

    $data = array('branding' => array(
      'image' => ninja::add_path('brands/' . brand::DEFAULT_IMAGE, false, true),
      'label' => ''
    ));

    Event::run('ninja.get_branding', $data);

    return sprintf(
      '<img class="brand-icon" src="%s" />' .
      '<span class="brand-label">%s</span>' .
      '<span class="brand-aligner"></span>',
      $host . $data["branding"]["image"], $data["branding"]["label"]
    );

  }
}