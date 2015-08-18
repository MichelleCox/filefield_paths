<?php

/**
 * @file
 * Contains \Drupal\filefield_paths\FileFieldPathsManager.
 */

namespace Drupal\filefield_paths;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;
use Drupal\Core\Utility\Token;
use Drupal\file\FileInterface;

/**
 * Class FileFieldPathsManager provides field processing.
 *
 * @todo Processing should be cleaned up.
 */
class FileFieldPathsManager {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * String cleaning service.
   *
   * @var \Drupal\filefield_paths\FileFieldPathsClean
   */
  protected $cleanService;

  /**
   * Holds the settings for the field being processed.
   *
   * @var Array
   */
  protected $fieldPathSettings;

  /**
   * Constructs FileFieldPathsManager object.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   * @param \Drupal\filefield_paths\FileFieldPathsClean $clean
   *   The string clean library.
   */
  public function __construct(Token $token, TransliterationInterface $transliteration, FileFieldPathsClean $clean) {
    $this->token = $token;
    $this->transliteration = $transliteration;
    $this->cleanService = $clean;
  }
  
  /**
   * Sets the property that holds the settings for the field in processing.
   *
   * @param array $settings
   */
  protected function setFieldPathSettings(array $settings) {
    $this->fieldPathSettings = $settings;
  }

  /*
   * Finds all the file based fields on a content entity and sends them off
   * to be processed.
   */
  public function processContentEntity(ContentEntityInterface $container_entity) {
    if ($container_entity instanceof ContentEntityInterface) {
      // Get a list of the types of fields that have files. (File, integer, video)
      $field_types = _filefield_paths_get_field_types();

      // Get a list of the fields on this entity.
      $fields = $container_entity->getFields();

      // Iterate through all the fields looking for ones in our list.
      foreach ($fields as $key => $field) {
        // Get the field definition which holds the type and our settings.
        $field_info = $field->getFieldDefinition();

        // Get the field type, ie: file.
        $field_type = $field_info->getType();

        // Check the field type against our list of fields.
        if (isset($field_type) && in_array($field_type, $field_types)) {
          $this->processField($container_entity, $field_info);
        }
      }
    }
  }

  /**
   * Finds all the files on the field and sends them to be processed.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $container_entity
   * @param \Drupal\Core\Config\Entity\ThirdPartySettingsInterface $field_info
   */
  protected function processField(ContentEntityInterface $container_entity, ThirdPartySettingsInterface $field_info) {
    // Retrieve the settings we added to the field.
    $this->setFieldPathSettings($field_info->getThirdPartySettings('filefield_paths'));

    // If FFP is enabled on this field, process it.
    if ($this->fieldPathSettings['enabled']) {

      // Get the machine name of the field.
      $field_name = $field_info->field_name;

      // Go through each item on the field.
      foreach ($container_entity->{$field_name} as $item) {
        // Get the file entity associated with the item.
        $file_entity = $item->entity;

        // Process the file.
        $this->processFile($container_entity, $file_entity);
      }
    }
  }

  /**
   * Cleans up path and name, moves to new location, and renames.
   *
   * @param $file_entity
   */
  protected function processFile(ContentEntityInterface $container_entity, FileInterface $file_entity) {
    if ($this->fileNeedsUpdating($file_entity)) {
      // Retrieve the path/name strings with the tokens from settings.
      $tokenized_path = $this->fieldPathSettings['filepath'];
      $tokenized_filename = $this->fieldPathSettings['filename'];
      // @todo Add language from entity for token/transliterate calls.

      // Replace tokens.
      $entity_type = $container_entity->getEntityTypeId();
      $data = array($entity_type => $container_entity, 'file' => $file_entity);
      $path = $this->token->replace($tokenized_path, $data);
      $filename = $this->token->replace($tokenized_filename, $data);

      // Transliterate.
      if ($this->fieldPathSettings['path_options']['transliterate_path']) {
        $path = $this->transliteration->transliterate($path);
      }

      if ($this->fieldPathSettings['name_options']['transliterate_filename']) {
        $filename = $this->transliteration->transliterate($filename);
      }

      // Clean string to remove URL unfriendly characters.
      if ($this->fieldPathSettings['path_options']['clean_path']) {
        $path_segments = explode("/", $path);
        $cleaned_segments = array();
        foreach ($path_segments as $segment) {
          $cleaned_segments[] = $this->cleanService->cleanString($segment);
        }
        $path = implode("/", $cleaned_segments);
      }

      if ($this->fieldPathSettings['name_options']['clean_filename']) {
        $name_parts = pathinfo($filename);
        $cleaned_base = $this->cleanService->cleanString($name_parts['filename']);
        $cleaned_extension = $this->cleanService->cleanString($name_parts['extension']);

        $filename = $cleaned_base . '.' . $cleaned_extension;
      }

      // @TODO: Sanity check to be sure we don't end up with an empty path or name.
      // If path is empty, just change filename?
      // If filename is empty, use original?

      // Move the file to its new home.
      $destination = file_build_uri($path);
      file_prepare_directory($destination, FILE_CREATE_DIRECTORY);
      file_move($file_entity, $destination . DIRECTORY_SEPARATOR . $filename);
    }
  }

  /**
   * Determines if a given file should be updated.
   *
   * @param FileInterface $file_entity
   * @return bool
   */
  protected function fileNeedsUpdating(FileInterface $file_entity) {
    // If this field is using active updating, then we always update.
    // If the file is newly uploaded, then we update. Otherwise, leave it alone.
    // Note: $file_entity->isNew() was not accurate for this.
    $file_is_new = ($file_entity->getChangedTime() == REQUEST_TIME);
    return ($this->fieldPathSettings['active_updating'] || $file_is_new);
  }

}
