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
  public static function get() {

    // Default branding, ninja in community, op5 banana otherwise
    $image = ninja::add_path('brands/' . brand::DEFAULT_IMAGE, false, true);
    $label = "";

    op5MayI::instance()->run("monitor.system.brand:info.icon", array(), $messages, $perfdata);

    if (!empty($messages)) {

      $label = $messages[0];
      $format = 'brands/%s.png';

      try {
        $image = ninja::add_path(sprintf($format, $label), false, false);
        $label = "";
      } catch (FileLookupErrorException $e) {
        // If there is no image, use default image already set
        // and the message as a label for that image.
      }

    }

    return sprintf(
      '<img class="brand-icon" src="%s" />' .
      '<span class="brand-label">%s</span>' .
      '<span class="brand-aligner"></span>',
      $image, $label
    );

  }
}