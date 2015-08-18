<?php
// @TODO Make batch update into a usable service class.

/**
 * Set batch process to update File (Field) Paths.
 *
 * @param $instance
 */
function filefield_paths_batch_update($instance) {
  $query = new EntityFieldQuery();
  $result = $query->entityCondition('entity_type', $instance['entity_type'])
    ->entityCondition('bundle', array($instance['bundle']))
    ->fieldCondition($instance['field_name'])
    ->execute();
  $objects = array_keys($result[$instance['entity_type']]);

// Create batch.
  $batch = array(
    'title' => t('Updating File (Field) Paths'),
    'operations' => array(
      array('_filefield_paths_batch_update_process', array($objects, $instance))
    ),
  );
  batch_set($batch);
}

/**
 *
 */
function _filefield_paths_batch_update_process($objects, $instance, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['max'] = count($objects);
    $context['sandbox']['objects'] = $objects;
  }

// Process nodes by groups of 5.
  $count = min(5, count($context['sandbox']['objects']));
  for ($i = 1; $i <= $count; $i++) {
// For each oid, load the object, update the files and save it.
    $oid = array_shift($context['sandbox']['objects']);
    $entity = current(\Drupal::entityManager()->getStorage($instance['entity_type'], array($oid)));

// Enable active updating if it isn't already enabled.
    $active_updating = $instance['settings']['filefield_paths']['active_updating'];
    if (!$active_updating) {
      $instance['settings']['filefield_paths']['active_updating'] = TRUE;
      $instance->save();
    }

// Invoke File (Field) Paths implementation of hook_entity_update().
    filefield_paths_entity_update($entity, $instance['entity_type']);

// Restore active updating to it's previous state if necessary.
    if (!$active_updating) {
      $instance['settings']['filefield_paths']['active_updating'] = $active_updating;
      $instance->save();
    }

// Update our progress information.
    $context['sandbox']['progress']++;
  }

// Inform the batch engine that we are not finished,
// and provide an estimation of the completion level we reached.
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}
