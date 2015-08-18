<?php

/**
 * @file
 * Contains \Drupal\filefield_paths\Tests\FileFieldPathsTextReplaceTestCase.
 */

namespace Drupal\filefield_paths\Tests;

/**
 * Provides methods specifically for testing File module's field handling.
 *
 * @group filefield_paths
 */
class FileFieldPathsTextReplaceTestCase extends FileFieldPathsTestCase {

  /**
   * @todo convert test.
   */
  public static function getInfo() {
    return array(
      'name'        => 'Text replace functionality',
      'description' => 'Tests text replace functionality.',
      'group'       => 'File (Field) Paths',
    );
  }

  /**
   * Test text replace with a basic file upload.
   */
  public function testUploadFile() {
    $langcode = LANGUAGE_NONE;

    // Create a File field with 'node/[node:nid]' as the File path and
    // '[node:nid].txt' as the File name,
    $field_name                                                 = strtolower($this->randomName());
    $instance_settings['filefield_paths']['file_path']['value'] = 'node/[node:nid]';
    $instance_settings['filefield_paths']['file_name']['value'] = '[node:nid].txt';
    $this->createFileField($field_name, $this->content_type, array(), $instance_settings);

    // Upload a file and reference the original path to the file in the body
    // field.
    $test_file            = $this->getTestFile('text');
    $original_destination = file_destination($test_file->uri, FILE_EXISTS_RENAME);
    $edit                 = array(
      'title'                              => $this->randomName(),
      "body[{$langcode}][0][value]"        => $original_destination,
      "files[{$field_name}_{$langcode}_0]" => drupal_realpath($test_file->uri),
    );
    $this->drupalPost("node/add/{$this->content_type}", $edit, t('Save'));

    // Get created Node ID.
    $matches = array();
    preg_match('/node\/([0-9]+)/', $this->getUrl(), $matches);
    $nid = $matches[1];

    // Ensure body field has updated file path.
    $node = node_load($nid);
    $this->assert(strpos($node->body[$langcode][0]['value'], "public://node/{$nid}/{$nid}.txt") !== FALSE, t('File path replaced correctly in text field.'));
  }

}