<?php

/**
 * @file
 * Contains \Drupal\ea_import\Storage\ICalendarStorage.
 */

namespace Drupal\ea_import\Storage;

/**
 * Storage for ICalendar files.
 */
class ICalendarStorage {

  /**
   * Save an entry in the database.
   */
  public static function insert($entry) {
    $return_value = NULL;
    try {
      $return_value = db_insert('ea_import_icalendar')
        ->fields($entry)
        ->execute();
    }
    catch (\Exception $e) {
      \Drupal::logger('ea_import')->notice(t('db_insert failed. Message = %message', array(
        '%message' => $e->getMessage(),
      )));
      drupal_set_message(t('Failed to add ICalendar file.'), 'error');
    }
    return $return_value;
  }

  /**
   * Update an entry in the database.
   */
  public static function update($entry) {
    $count = NULL;
    try {
      $count = db_update('ea_import_icalendar')
        ->fields($entry)
        ->condition('iid', $entry['iid'])
        ->execute();
    }
    catch (\Exception $e) {
      \Drupal::logger('ea_import')->notice(t('db_update failed. Message = %message, query= %query', array(
        '%message' => $e->getMessage(),
        '%query' => isset($e->query_string) ? $e->query_string : NULL,
      )));
      drupal_set_message(t('Failed to update ICalendar file.'), 'error');
    }
    return $count;
  }

  /**
   * Delete an entry from the database.
   */
  public static function delete($entry) {
    db_delete('ea_import_icalendar')
      ->condition('iid', $entry['iid'])
      ->execute();
  }

  /**
   * Read from the database.
   */
  public static function load(array $entry) {
    $select = db_select('ea_import_icalendar', 'icalendar');
    $select->fields('icalendar');
    // Add each field and value as a condition to this query.
    foreach ($entry as $field => $value) {
      $select->condition($field, $value);
    }
    // Return the result in object format.
    return $select->execute()->fetchAll();
  }

  /**
   * Read from the database, with tablesort.
   */
  public static function loadSorted($header = array(), $limit = 20) {
    // Read all fields from the ea_people table.
    $select = db_select('ea_import_icalendar', 'icalendar')
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit($limit)
      ->fields('icalendar');
    // Return the result in object format.
    return $select
     ->orderByHeader($header)
     ->execute();
  }
}
