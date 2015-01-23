<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Helper to get the proper complete URL to the server
 */
class brand {

  const DEFAULT_IMAGE = 'default.png';

  public static function get() {

    $image = ninja::add_path('brands/' . brand::DEFAULT_IMAGE, false, true);
    $label = "";

    op5MayI::instance()->run("monitor.system.brand:info.icon", false, $messages, $perfdata);

    if (!empty($messages)) {

      $label = $messages[0];
      $format = 'brands/%s.png';

      try {
        $image = ninja::add_path(sprintf($format, $label), false, false);
        $label = "";
      } catch (FileLookupErrorException $e) {}

    }

    return sprintf(
      '<img class="brand-icon" src="%s" />' .
      '<span class="brand-label">%s</span>' .
      '<span class="brand-aligner"></span>',
      $image, $label
    );

  }
}