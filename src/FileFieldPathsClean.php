<?php

/**
 * @file
 * Contains the \Drupal\filefield_paths\FileFieldPathsClean class.
 */

namespace Drupal\filefield_paths;

/**
 * Cleans URL-unfriendly characters out of strings using PathAuto or built-in.
 */
class FileFieldPathsClean {

  /**
   * Gatekeeper method to direct to either the PathAuto clean or built in.
   *
   * @param $string
   * @return $string
   */
  public function cleanString($string) {
    if (\Drupal::moduleHandler()->moduleExists('pathauto')) {
      return $this->pathAutoClean($string);
    }
    else {
      return $this->simpleClean($string);
    }
  }

  /**
   * Clean string using PathAuto.
   *
   * @param $string
   * @return $string
   */
  protected function pathAutoClean($string) {
    $pathauto_manager = \Drupal::service('pathauto.manager');
    $string = $pathauto_manager->cleanString($string);

    return $string;
  }

  /**
   * Clean string using built in tools.
   *
   * @param $string
   * @return $string
   */
  protected function simpleClean($string) {
    // @TODO: See if this can be enhanced but still kept simple.
    return preg_replace('/[^a-z0-9]+/', '-', strtolower($string));
  }

}
