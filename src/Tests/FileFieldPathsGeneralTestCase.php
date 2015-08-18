<?php

/**
 * @file
 * Contains \Drupal\filefield_paths\Tests\FileFieldPathsGeneralTestCase.
 */

namespace Drupal\filefield_paths\Tests;

/**
 * Provides methods specifically for testing File module's field handling.
 *
 * @group filefield_paths
 */
class FileFieldPathsGeneralTestCase extends FileFieldPathsTestCase {

  /**
   * @todo convert test.
   */
  public static function getInfo() {
    return array(
      'name'        => 'General functionality',
      'description' => 'Test general functionality.',
      'group'       => 'File (Field) Paths',
    );
  }

  /**
   * Test that the File (Field) Paths UI works as expected.
   */
  public function testAddField() {
    // Create a File field.
    $field_name = strtolower($this->randomName());
    $this->createFileField($field_name, $this->content_type);

    // Ensure File (Field) Paths settings are present.
    $this->drupalGet("admin/structure/types/manage/{$this->content_type}/fields/{$field_name}");
    $this->assertText('Enable File (Field) Paths?', t('File (Field) Path settings are present.'));
  }

  /**
   * Test a basic file upload with File (Field) Paths.
   */
  public function testUploadFile() {
    // Create a File field with 'node/[node:nid]' as the File path and
    // '[node:nid].[file:ffp-extension-original]' as the File name.
    $field_name                                                 = strtolower($this->randomName());
    $instance_settings['filefield_paths']['file_path']['value'] = 'node/[node:nid]';
    $instance_settings['filefield_paths']['file_name']['value'] = '[node:nid].[file:ffp-extension-original]';
    $this->createFileField($field_name, $this->content_type, array(), $instance_settings);

    // Create a node with a test file.
    $test_file = $this->getTestFile('text');
    $nid       = $this->uploadNodeFile($test_file, $field_name, $this->content_type);

    // Ensure that the File path has been processed correctly.
    $this->assertRaw("{$this->public_files_directory}/node/{$nid}/{$nid}.txt", t('The File path has been processed correctly.'));
  }

  /**
   * Tests a multivalue file upload with File (Field) Paths.
   */
  public function testUploadFileMultivalue() {
    $langcode = LANGUAGE_NONE;

    // Create a multivalue File field with 'node/[node:nid]' as the File path
    // and '[file:fid].txt' as the File name.
    $field_name                                                 = strtolower($this->randomName());
    $field_settings['cardinality']                              = FIELD_CARDINALITY_UNLIMITED;
    $instance_settings['filefield_paths']['file_path']['value'] = 'node/[node:nid]';
    $instance_settings['filefield_paths']['file_name']['value'] = '[file:fid].txt';
    $this->createFileField($field_name, $this->content_type, $field_settings, $instance_settings);

    // Create a node with three (3) test files.
    $text_files = $this->drupalGetTestFiles('text');
    $this->drupalGet("node/add/{$this->content_type}");
    $this->drupalPost(NULL, array("files[{$field_name}_{$langcode}_0]" => drupal_realpath($text_files[0]->uri)), t('Upload'));
    $this->drupalPost(NULL, array("files[{$field_name}_{$langcode}_1]" => drupal_realpath($text_files[1]->uri)), t('Upload'));
    $edit = array(
      'title'                              => $this->randomName(),
      "files[{$field_name}_{$langcode}_2]" => drupal_realpath($text_files[1]->uri),
    );
    $this->drupalPost(NULL, $edit, t('Save'));

    // Get created Node ID.
    $matches = array();
    preg_match('/node\/([0-9]+)/', $this->getUrl(), $matches);
    $nid = $matches[1];

    // Ensure that the File path has been processed correctly.
    $this->assertRaw("{$this->public_files_directory}/node/{$nid}/1.txt", t('The first File path has been processed correctly.'));
    $this->assertRaw("{$this->public_files_directory}/node/{$nid}/2.txt", t('The second File path has been processed correctly.'));
    $this->assertRaw("{$this->public_files_directory}/node/{$nid}/3.txt", t('The third File path has been processed correctly.'));
  }

}