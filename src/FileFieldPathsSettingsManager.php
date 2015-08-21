<?php

namespace Drupal\filefield_paths;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;


class FileFieldPathsSettingsManager {
  protected $cleanService;
  protected $tokenService;
  protected $transliterateService;
  protected $fieldPathSettings;

  public function __construct(FileFieldPathsClean $clean,
                              FileFieldPathsToken $token,
                              FileFieldPathsTransliterate $transliterate) {
    $this->cleanService = $clean;
    $this->tokenService = $token;
    $this->transliterateService = $transliterate;
  }

  /**
   * Performs form alterations to the field settings form.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterSettingsForm(array &$form, FormStateInterface $form_state) {
    $file_field_types = _filefield_paths_get_field_types();
    $field_config = $form_state->getBuildInfo()['callback_object']->getEntity();

    if (in_array($field_config->getType(), $file_field_types)) {
      // Get our 3rd party settings to use as defaults on the form.
      $defaults = $field_config->getThirdPartySettings('filefield_paths');

      // FFP fieldset.
      $form['third_party_settings']['filefield_paths'] = array(
        '#type' => 'details',
        '#title' => t('File (Field) Paths settings'),
        '#open' => TRUE,
      );

      // Enable / disable.
      $default = isset($defaults['enabled']) ? $defaults['enabled'] : FALSE;
      $form['third_party_settings']['filefield_paths']['enabled'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable File (Field) Paths?'),
        '#default_value' => $default,
        '#weight' => -10,
      );

      // @TODO: Hiding directory field doesn't work.
      // Hide standard File directory field.
      $form['third_party_settings']['settings']['file_directory']['#states'] = array(
        '#states' => array(
          '!visible' => array(
            ':input[name="field[third_party_settings][filefield_paths][enabled]"]' => array('value' => '1'),
          )
        ),
      );

      // Token browser.
      $form['third_party_settings']['filefield_paths']['token_tree'] = $this->tokenService->tokenBrowser();

      // File path.
      $default = isset($defaults['filepath']) ? $defaults['filepath'] : '';
      $form['third_party_settings']['filefield_paths']['filepath'] = array(
        '#type' => 'textfield',
        '#title' => t('File path'),
        '#maxlength' => 512,
        '#size' => 128,
        '#element_validate' => array('_file_generic_settings_file_directory_validate'),
        '#default_value' => $default,
      );

      // File path options fieldset.
      $form['third_party_settings']['filefield_paths']['path_options'] = array(
        '#type' => 'details',
        '#title' => t('File path options'),
        '#open' => FALSE,
      );

      // Clean up path.
      $default = isset($defaults['path_options']['clean_path']) ? $defaults['path_options']['clean_path'] : TRUE;
      $form['third_party_settings']['filefield_paths']['path_options']['clean_path'] = $this->getStringCleanElement('filepath', $default);

      // Transliterate path.
      $default = isset($defaults['path_options']['transliterate_path']) ? $defaults['path_options']['transliterate_path'] : TRUE;
      $form['third_party_settings']['filefield_paths']['path_options']['transliterate_path'] = $this->getTransliterationElement('filepath', $default);

      // File name.
      $default = (isset($defaults['filename']) && !empty($defaults['filename'])) ? $defaults['filename'] : '[file:ffp-name-only-original].[file:ffp-extension-original]';
      $form['third_party_settings']['filefield_paths']['filename'] = array(
        '#type' => 'textfield',
        '#title' => t('File name'),
        '#maxlength' => 512,
        '#size' => 128,
        '#element_validate' => array('_file_generic_settings_file_directory_validate'),
        '#default_value' => $default,
      );

      // File name options fieldset.
      $form['third_party_settings']['filefield_paths']['name_options'] = array(
        '#type' => 'details',
        '#title' => t('File name options'),
        '#open' => FALSE,
      );

      // Clean up filename.
      $default = isset($defaults['name_options']['clean_filename']) ? $defaults['name_options']['clean_filename'] : TRUE;
      $form['third_party_settings']['filefield_paths']['name_options']['clean_filename'] = $this->getStringCleanElement('filename', $default);

      // Transliterate filename.
      $default = isset($defaults['name_options']['transliterate_filename']) ? $defaults['name_options']['transliterate_filename'] : TRUE;
      $form['third_party_settings']['filefield_paths']['name_options']['transliterate_filename'] = $this->getTransliterationElement('filename', $default);

      // Retroactive updates.
      $default = isset($defaults['retroactive_update']) ? $defaults['retroactive_update'] : FALSE;
      $form['third_party_settings']['filefield_paths']['retroactive_update'] = array(
        '#type' => 'checkbox',
        '#title' => t('Retroactive update'),
        '#description' => t('Move and rename previously uploaded files.') . '<div>' . t('<strong class="warning">Warning:</strong> This feature should only be used on developmental servers or with extreme caution.') . '</div>',
        '#weight' => 11,
        '#default_value' => $default,
      );

      // Active updating.
      $default = isset($defaults['active_updating']) ? $defaults['active_updating'] : FALSE;
      $form['third_party_settings']['filefield_paths']['active_updating'] = array(
        '#type' => 'checkbox',
        '#title' => t('Active updating'),
        '#default_value' => $default,
        '#description' => t('Actively move and rename previously uploaded files as required.') . '<div>' . t('<strong class="warning">Warning:</strong> This feature should only be used on developmental servers or with extreme caution.') . '</div>',
        '#weight' => 12
      );

      // @TODO: Uncomment this when retroactive updates are working.
      // $form['#submit'][] = 'filefield_paths_form_submit';
    }

  }

  /**
   * Returns the form element for the PathAuto checkbox in FFP settings.
   *
   * @param $setting
   *   File path or File name.
   * @param $default
   *   Default or existing value for the form element.
   * @return array
   */
  protected function getStringCleanElement($setting, $default) {
    if (\Drupal::moduleHandler()->moduleExists('pathauto')) {
      $description = t('Cleanup %setting using <a href="@pathauto">Pathauto settings</a>.', array(
        '%setting' => $setting,
        '@pathauto' => Url::fromRoute('pathauto.settings.form')->toString()));
    }
    else {
      $description = t('Basic cleanup such as changing non alphanumeric characters to hyphens. More advanced cleanup can be done if PathAuto is installed.');
    }

    return array(
      '#type' => 'checkbox',
      '#title' => t('Clean up %setting', array('%setting' => $setting)),
      '#default_value' => $default,
      '#description' => $description,
      '#disabled' => FALSE,
    );
  }

  /**
   * Returns the form element for the Transliteration checkbox in FFP settings.
   *
   * @param $setting
   *   File path or File name.
   * @param $default
   *   Default or existing value for the form element.
   * @return array
   */
  protected function getTransliterationElement($setting, $default) {
    if (\Drupal::moduleHandler()->moduleExists('transliteration')) {
      $description = t('Provides one-way string transliteration (romanization) and cleans the %setting during upload by replacing unwanted characters.', array('%setting' => $setting));
    }
    else {
      $description = t('The Transliteration module is not installed, but you can use transliteration functionality from Drupal Core.');
    }

    return array(
      '#type' => 'checkbox',
      '#title' => t('Cleanup using Transliteration'),
      '#default_value' => $default,
      '#description' => $description,
      '#disabled' => FALSE,
    );
  }

}
