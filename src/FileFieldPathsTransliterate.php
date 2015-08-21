<?php

/**
 * @file
 * Contains the \Drupal\filefield_paths\FileFieldPathsTransliterate class.
 */

namespace Drupal\filefield_paths;

/**
 * Transliterates strings. Uses core service or contrib Transliterate module.
 */
class FileFieldPathsTransliterate {

  /**
   * Gatekeeper function to direct to either core or contrib transliteration.
   *
   * @param $string
   * @return string
   */
  public function transliterate($string) {
    if (\Drupal::moduleHandler()->moduleExists('transliterate')) {
      return $this->contribTransliterate($string);
    }
    else {
      return $this->coreTransliterate($string);
    }
  }

  /**
   * Transliterate the string using the core service.
   *
   * @param $string
   * @return string
   */
  protected function coreTransliterate($string) {
    // Use the current default interface language.
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    // Instantiate the transliteration class.
    $trans = \Drupal::transliteration();

    return $trans->transliterate($string, $langcode);
  }

  /**
   * Transliterate the string using the contrib Transliterate module.
   *
   * @param $string
   * @return string
   */
  protected function contribTransliterate($string) {
    // @TODO: Add contrib Transliterate integration when it is ready.
    // For now, just redirect to the core replacement to avoid breaking sites
    // where Transliterate is installed.
    return $this->coreTransliterate($string);
  }

}  
