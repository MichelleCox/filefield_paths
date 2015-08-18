<?php

/**
 * @file
 * Contains \Drupal\filefield_paths\Tests\FileFieldTestCase.
 */

namespace Drupal\filefield_paths\Tests;

use Drupal\file\Tests\FileFieldTestBase;

/**
 * Provides methods specifically for testing File module's field handling.
 */
class FileFieldPathsTestCase extends FileFieldTestBase {

  var $content_type = NULL;
  var $public_files_directory = NULL;

  /**
   * @inheritdoc
   */
  function _setUp() {
    // Setup required modules.
    parent::setUp(array('filefield_paths', 'image'));

    // Create a content type.
    $content_type       = $this->drupalCreateContentType();
    $this->content_type = $content_type->label();
  }

  /**
   * Creates a new image field.
   *
   * @param $name
   *   The name of the new field (all lowercase), exclude the "field_" prefix.
   * @param $type_name
   *   The node type that this field will be added to.
   * @param $field_settings
   *   A list of field settings that will be added to the defaults.
   * @param $instance_settings
   *   A list of instance settings that will be added to the instance defaults.
   * @param $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   */
  function createImageField($name, $type_name, $field_settings = array(), $instance_settings = array(), $widget_settings = array()) {
    $field             = array(
      'field_name'  => $name,
      'type'        => 'image',
      'settings'    => array(),
      'cardinality' => !empty($field_settings['cardinality']) ? $field_settings['cardinality'] : 1,
    );
    $field['settings'] = array_merge($field['settings'], $field_settings);
    field_create_field($field);

    $instance                       = array(
      'field_name'  => $name,
      'label'       => $name,
      'entity_type' => 'node',
      'bundle'      => $type_name,
      'required'    => !empty($instance_settings['required']),
      'settings'    => array(),
      'widget'      => array(
        'type'     => 'image_image',
        'settings' => array(),
      ),
    );
    $instance['settings']           = array_merge($instance['settings'], $instance_settings);
    $instance['widget']['settings'] = array_merge($instance['widget']['settings'], $widget_settings);
    field_create_instance($instance);
  }

}