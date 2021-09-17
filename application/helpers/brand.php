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
   * The image to use when in menu
   */
  const MENU_IMAGE = 'logo_op5monitor.svg';

  /**
   * Generates brading based based on license, if any, and
   * if the image exists, otherwise return generic branding
   * blob
   *
   * @return string A HTML blob to use in branding
   */
  public static function get($host = "", $use_label = true, $is_menu = false) {
    $product_image = ($is_menu ? brand::MENU_IMAGE : brand::DEFAULT_IMAGE);
    $data = array('branding' => array(
      'image' => ninja::add_path('brands/' . $product_image, false, true),
      'label' => ''
    ));

    if($is_menu == false) {
      Event::run('ninja.get_branding', $data);
    }

    return sprintf(
      '<img class="brand-icon" src="%s" />' .
      '<span class="brand-label">%s</span>' .
      '<span class="brand-aligner"></span>',
      $host . $data["branding"]["image"],
      ($use_label) ? $data["branding"]["label"] : ""
    );

  }
}