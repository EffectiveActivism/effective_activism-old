<?php

namespace Drupal\ea_imports\Parser;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_events\Entity\Event;
use Drupal\ea_events\Entity\EventRepeater;
use Drupal\ea_tasks\Entity\Task;
use Drupal\ea_people\Entity\Person;
use Drupal\ea_data\Entity\Data;
use Drupal\ea_results\Entity\Result;
use Drupal\file\Entity\File;

/**
 * Entity parsing functions.
 */
class EntityParser {

  /**
   * Filters standard entity fields.
   *
   * @param string $type
   *   The entity type.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return array
   *   A filtered array of fields.
   */
  private function getFields($type, $bundle = NULL) {
    if (empty($bundle)) {
      $bundle = $type;
    }
    $fields = array_keys(\Drupal::entityManager()->getFieldDefinitions($type, $bundle));
    // Do not include standard fields.
    unset($fields[array_search('id', $fields)]);
    unset($fields[array_search('uuid', $fields)]);
    unset($fields[array_search('user_id', $fields)]);
    unset($fields[array_search('status', $fields)]);
    unset($fields[array_search('langcode', $fields)]);
    unset($fields[array_search('default_langcode', $fields)]);
    unset($fields[array_search('created', $fields)]);
    unset($fields[array_search('changed', $fields)]);
    // Also exclude revision fields.
    unset($fields[array_search('revision_id', $fields)]);
    unset($fields[array_search('revision_created', $fields)]);
    unset($fields[array_search('revision_user', $fields)]);
    unset($fields[array_search('revision_log_message', $fields)]);
    $fields = array_values($fields);
    return $fields;
  }

  /**
   * Validates an entity.
   *
   * @param Entity $entity
   *   Entity to validate.
   *
   * @param array $ignoreErrors
   *   Validation errors to ignore.
   *
   * @return bool
   *   TRUE if entity has no violations, FALSE otherwise.
   */
  private function validateEntity($entity, $fieldsToIgnore = []) {
    $isValid = TRUE;
    if ($entity) {
      $this->errorMessages = [];
      foreach ($entity->validate() as $violation) {
        if (!in_array($violation->getPropertyPath(), $fieldsToIgnore)) {
          $isValid = FALSE;
        }
      }
    }
    return $isValid;
  }

  /**
   * Validates a task entity.
   *
   * @param string $type
   *   Data to validate as task entity.
   *
   * @return bool
   *   TRUE if task is valid, FALSE otherwise.
   */
  public function validateTask($type) {
    $values = [
      $type,
      NULL,
    ];
    $fields = $this->getFields('task');
    $data = array_combine($fields, $values);
    return $this->validateEntity(Task::create($data));
  }

  /**
   * Validates a participant entity.
   *
   * @param array $values
   *   Data to validate as participant entity.
   *
   * @return bool
   *   TRUE if participant is valid, FALSE otherwise.
   */
  public function validateParticipant($values) {
    $fields = $this->getFields('person');
    $data = array_combine($fields, $values);
    return $this->validateEntity(Person::create($data));
  }

  /**
   * Validates a result.
   *
   * @param array $values
   *   Data to validate as result entity.
   *
   * @return bool
   *   TRUE if result is valid, FALSE otherwise.
   */
  public function validateResult($values, $bundle) {
    // Make sure the type is valid.
    if (!in_array($bundle, array_values(array_keys(\Drupal::entityManager()->getBundleInfo('result'))))) {
      $this->errorMessages[] = t('Illegal bundle');
      return FALSE;
    }
    $fields = $this->getFields('result', $bundle);
    $fieldsToIgnore = [];
    foreach ($fields as $key => $field) {
      // Create any data entities identified by field name 'field_*'.
      if (strpos($field, 'field_') === 0) {
        $dataType = substr($field, strlen('field_'));
        $this->validateData([
          $dataType,
          $values[$key],
        ], $dataType);
        // Overwrite value with corresponding data entity.
        $values[$key] = NULL;
        // Do not validate this field for the result entity.
        $fieldsToIgnore[] = $field;
      }
    }
    $data = array_combine($fields, $values);
    return $this->validateEntity(Result::create($data), $fieldsToIgnore);
  }

  /**
   * Validates a data entity.
   *
   * @param array $values
   *   Data to validate as data entity.
   *
   * @return bool
   *   TRUE if data is valid, FALSE otherwise.
   */
  public function validateData($values, $bundle) {
    $fields = $this->getFields('data', $bundle);
    $data = array_combine($fields, $values);
    return $this->validateEntity(Data::create($data));
  }

  /**
   * Validates an event repeater entity.
   *
   * @param string $rrule
   *   Data to validate as event repeater entity.
   *
   * @return bool
   *   TRUE if event repeater is valid, FALSE otherwise.
   */
  public function validateEventRepeater($rrule) {
    $rruleParser = new RruleParser($rrule);
    $data = $rruleParser->getEventRepeaterValues();
    return $this->validateEntity(EventRepeater::create($data));
  }

  /**
   * Validates an event entity.
   *
   * @param array $data
   *   Data to validate as an event entity.
   *
   * @return bool
   *   TRUE if event is valid, FALSE otherwise.
   */
  public function validateEvent($values) {
    $fields = $this->getFields('event');
    $data = array_combine($fields, $values);
    return $this->validateEntity(Event::create($data), ['event_repeater']);
  }

  /**
   * Imports an event repeater entity.
   *
   * @param string $rrule
   *   RRule to import as an event repeater.
   *
   * @return int|bool
   *   The event repeater id or FALSE if import failed.
   */
  public function importEventRepeater($rrule) {
    $fields = $this->getFields('event_repeater');
    $rruleParser = new RruleParser($rrule);
    $values = $rruleParser->getEventRepeaterValues();
    $data = array_combine($fields, $values);
    $entity = EventRepeater::create($data);
    if ($entity->save()) {
      return $entity;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Imports a default event repeater entity.
   *
   * @return int|bool
   *   The event repeater id or FALSE if import failed.
   */
  public function importDefaultEventRepeater() {
    $data = EventRepeater::DEFAULT_VALUES;
    $entity = EventRepeater::create($data);
    if ($entity->save()) {
      return $entity;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Imports a task entity.
   *
   * @param string $type
   *   Type of task.
   *
   * @return int|bool
   *   The task id or FALSE if import failed.
   */
  public function importTask($type) {
    $values = [
      $type,
      NULL,
    ];
    $fields = $this->getFields('task');
    $data = array_combine($fields, $values);
    $entity = Task::create($data);
    if ($entity->save()) {
      return $entity;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Imports a participant entity.
   *
   * @param array $values
   *   Values to import as a participant entity.
   *
   * @return int|bool
   *   The participant id or FALSE if import failed.
   */
  public function importParticipant($values) {
    $fields = $this->getFields('person');
    $data = array_combine($fields, $values);
    $entity = Person::create($data);
    if ($entity->save()) {
      return $entity;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Imports a result entity.
   *
   * @param array $values
   *   Values to import as a result entity.
   *
   * @return int|bool
   *   The participants id or FALSE if import failed.
   */
  public function importResult($values, $bundle) {
    $fields = $this->getFields('result', $bundle);
    foreach ($fields as $key => $field) {
      // Create any data entities identified by field name 'field_*'.
      if (strpos($field, 'field_') === 0) {
        $dataType = substr($field, strlen('field_'));
        $dataEntity = $this->importData($values[$key], $dataType);
        // Overwrite value with corresponding data entity.
        $values[$key] = $dataEntity->id();
      }
    }
    $data = array_combine($fields, $values);
    $entity = Result::create($data);
    if ($entity->save()) {
      return $entity;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Imports a data entity.
   *
   * @param array $values
   *   Values to import as a data entity.
   *
   * @return int|bool
   *   The participants id or FALSE if import failed.
   */
  public function importData($dataValue, $bundle) {
    $fields = $this->getFields('data', $bundle);
    $data = array_combine($fields, [
      $bundle,
      $dataValue,
    ]);
    $entity = Data::create($data);
    if ($entity->save()) {
      return $entity;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Imports an event entity.
   *
   * @param array $values
   *   Values to import as an event.
   *
   * @return int|bool
   *   The event id or FALSE if import failed.
   */
  public function importEvent($values) {
    $fields = $this->getFields('event');
    $data = array_combine($fields, $values);
    $entity = Event::create($data);
    if ($entity->save()) {
      return $entity;
    }
    else {
      return FALSE;
    }
  }

}
