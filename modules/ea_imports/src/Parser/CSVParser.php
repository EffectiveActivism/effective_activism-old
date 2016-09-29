<?php

namespace Drupal\ea_imports\Parser;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_events\Entity\Event;
use Drupal\ea_events\Entity\EventRepeater;
use Drupal\ea_data\Entity\Data;
use Drupal\ea_activities\Entity\Activity;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Parses ICalendar.
 */
class CSVParser {

  /**
   * CSV file.
   *
   * @var FileInterface
   */
  private $file;

  /**
   * Parent grouping.
   *
   * @var Grouping
   */
  private $grouping;

  /**
   * The current line number.
   *
   * @var int
   */
  private $line;

  /**
   * The current column number.
   *
   * @var int
   */
  private $column;

  /**
   * The event entity field definitions.
   *
   * @var array
   */
  private $fieldDefinitions;

  /**
   * The parsed data.
   *
   * @var array
   */
  private $data;

  /**
   * Any validation error message.
   *
   * @var array
   */
  private $errorMessage;

  /**
   * Creates the CSVParser Object.
   *
   * @param string $file
   *   A CSV file.
   * @param int $gid
   *   The parent grouping id of the events.
   */
  public function __construct($file, $gid) {
    if (empty($file)) {
      return FALSE;
    }
    // Load file entity.
    $csv_file = File::load($file);
    $this->file = $csv_file;
    // Load the grouping.
    $this->grouping = Grouping::load($gid);
    // Set basefield definitions.
    $this->fieldDefinitions = $this->getFieldDefinitions('event');
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    try {
      $this->validateColumnNames();
      $this->validateData();
    }
    catch (ParserValidationException $exception) {
      switch ($exception->getMessage()) {
        case WRONG_COLUMN_COUNT:
          $this->errorMessage = t('The CSV file does not have the correct number of columns. Column names should be "@column_names_format"', ['@column_names_format' => implode(', ', array_keys($this->fieldDefinitions))]);
          break;

        case INVALID_HEADERS:
          $this->errorMessage = t('The CSV file does not contain valid column names. Column names should be "@column_names_format"', ['@column_names_format' => implode(', ', array_keys($this->fieldDefinitions))]);
          break;

        case REQUIRED_VALUE:
          $this->errorMessage = t('The CSV file contains a required value that is missing at line @line, column @column.', ['@line' => $exception->getDataLine(), '@column' => $exception->getDataColumn()]);
          break;

        case WRONG_INT_FORMAT:
          $this->errorMessage = t('The CSV file contains a value that is not formatted properly at line @line, column @column.', ['@line' => $exception->getDataLine(), '@column' => $exception->getDataColumn()]);
          break;

        case WRONG_DATE_FORMAT:
          $this->errorMessage = t('The CSV file contains a date that is not formatted properly at line @line, column @column.', ['@line' => $exception->getDataLine(), '@column' => $exception->getDataColumn()]);
          break;

        case WRONG_LOCATION_FORMAT:
          $this->errorMessage = $this->errorMessage = t('The CSV file contains a location that is not formatted properly at line @line, column @column. Location should be formatted as @valid_format', [
            '@line' => $exception->getDataLine(),
            '@column' => $exception->getDataColumn(),
            '@valid_format' => $exception->getAdditionalInformation(),
          ]);
          break;

        case WRONG_ENTITY_FORMAT:
          if (!empty($exception->getAdditionalInformation())) {
            $this->errorMessage = t('The CSV file contains a value that is not formatted properly at line @line, column @column. Value should be formatted as @valid_format', [
              '@line' => $exception->getDataLine(),
              '@column' => $exception->getDataColumn(),
              '@valid_format' => $exception->getAdditionalInformation(),
            ]);
          }
          else {
            $this->errorMessage = t('The CSV file contains a value that is not formatted properly at line @line, column @column.', [
              '@line' => $exception->getDataLine(),
              '@column' => $exception->getDataColumn(),
            ]);
          }
          break;

        case PERMISSION_DENIED:
          $this->errorMessage('The CSV file contains a row with an activity that is not permissable for this grouping at line @line, column @column', ['@line' => $this->line, '@column' => $this->column]);
          break;

        default:
          $this->errorMessage = t('The CSV file failed to validate.');
      }
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    if (($handle = fopen($this->file->getFileUri(), "r")) !== FALSE) {
      $this->line = 0;
      while (($row = fgetcsv($handle)) !== FALSE) {
        // Skip headers.
        if ($this->line === 0) {
          $this->line++;
          continue;
        }
        $columnNames = array_keys($this->fieldDefinitions);
        $values = [];
        foreach ($columnNames as $column => $name) {
          $this->column = $column;
          switch ($this->fieldDefinitions[$name]->getType()) {
            case 'datetime':
              // Format datetime object.
              $value = date(DATETIME_DATETIME_STORAGE_FORMAT, $row[$column]);
              break;

            case 'location':
              // Format location field.
              $value = json_decode($row[$column], TRUE);
              break;

            case 'entity_reference':
              // Unpack entity reference.
              $value = $this->unpackEntityReference($name, $row[$column], TRUE);
              break;

            default:
              $value = $row[$column];
          }
          if (!empty($value)) {
            $values[$name] = $value;
          }
        }
        // Add parent grouping to event.
        $values['grouping'] = $this->grouping->id();
        // Create event.
        try {
          $event = Event::create($values);
          $event->save();
        }
        catch (EntityStorageException $exception) {
          drupal_set_message(t('The CSV file contains a row that failed to import at line @line', ['@line' => $this->line]), 'error');
        }
        $this->line++;
      }
      fclose($handle);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getErrorMessage() {
    return $this->errorMessage;
  }

  /**
   * Filters definitions from the entity basefield definitions.
   *
   * @param string $type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   *
   * @return array
   *   An array of field definitions for the entity type.
   */
  private function getFieldDefinitions($type, $bundle = NULL) {
    if (empty($bundle)) {
      // For entities without bundles, set the bundle to the type.
      $bundle = $type;
    }
    $fieldDefinitions = \Drupal::entityManager()->getFieldDefinitions($type, $bundle);
    // Do not include standard fields.
    unset($fieldDefinitions['id']);
    unset($fieldDefinitions['uuid']);
    unset($fieldDefinitions['user_id']);
    unset($fieldDefinitions['status']);
    unset($fieldDefinitions['langcode']);
    unset($fieldDefinitions['created']);
    unset($fieldDefinitions['changed']);
    // Also exclude revision fields.
    unset($fieldDefinitions['revision_id']);
    unset($fieldDefinitions['revision_created']);
    unset($fieldDefinitions['revision_user']);
    unset($fieldDefinitions['revision_log_message']);
    // Do not include grouping field as this is taken from the Import entity.
    unset($fieldDefinitions['grouping']);
    return $fieldDefinitions;
  }

  /**
   * Validates column names.
   */
  private function validateColumnNames() {
    if (($handle = fopen($this->file->getFileUri(), "r")) !== FALSE) {
      $column_names = fgetcsv($handle);
      fclose($handle);
      if (count($column_names) < count($this->fieldDefinitions)) {
        throw new ParserValidationException(WRONG_COLUMN_COUNT);
      }
      elseif ($column_names !== array_keys($this->fieldDefinitions)) {
        throw new ParserValidationException(INVALID_HEADERS);
      }
    }
    else {
      throw new ParserValidationException();
    }
  }

  /**
   * Validate data.
   */
  private function validateData() {
    if (($handle = fopen($this->file->getFileUri(), "r")) !== FALSE) {
      $this->line = 0;
      // Run through every row to validate it.
      while (($row = fgetcsv($handle)) !== FALSE) {
        // Skip column names.
        if ($this->line === 0) {
          $this->line++;
          continue;
        }
        $this->column = 0;
        foreach ($row as $cell) {
          // Validate by field definition.
          $this->validateValue($cell, $this->fieldDefinitions[array_keys($this->fieldDefinitions)[$this->column]]);
          $this->column++;
        }
        $this->line++;
      }
      fclose($handle);
    }
    else {
      throw new ParserValidationException();
    }
  }

  /**
   * Validate a value by the corresponding field definition.
   *
   * @param string $value
   *   A value to validate.
   * @param BaseFieldDefinition $fieldDefinition
   *   The field definition to validate against.
   */
  private function validateValue($value, BaseFieldDefinition $fieldDefinition = NULL) {
    if (empty($fieldDefinition)) {
      throw new ParserValidationException(INVALID_HEADERS);
    }
    if (empty($value) && $fieldDefinition->isRequired()) {
      // Allow event repeater value to be empty.
      if ($fieldDefinition->getName() !== 'event_repeater') {
        throw new ParserValidationException(REQUIRED_VALUE, $this->line + 1, $this->column + 1);
      }
    }
    switch ($fieldDefinition->getType()) {
      case 'integer':
        if (!is_int($value)) {
          throw new ParserValidationException(WRONG_INT_FORMAT, $this->line + 1, $this->column + 1);
        }
        break;

      case 'datetime':
        if (!is_numeric($value)) {
          throw new ParserValidationException(WRONG_DATE_FORMAT, $this->line + 1, $this->column + 1);
        }
        break;

      case 'location':
        if (!empty($value)) {
          $location_format = ['address', 'extra_information'];
          $location = json_decode($value, TRUE);
          if (empty($location) || !is_array($location)) {
            throw new ParserValidationException(WRONG_LOCATION_FORMAT, $this->line + 1, $this->column + 1, json_encode($location_format));
          }
          if (array_keys($location) !== $location_format) {
            throw new ParserValidationException(WRONG_LOCATION_FORMAT, $this->line + 1, $this->column + 1, json_encode($location_format));
          }
        }
        break;

      case 'entity_reference':
        if (!empty($value)) {
          // Load the base field definitions of the referenced entity type.
          $referecedEntityType = $fieldDefinition->getItemDefinition()->getSetting('target_type');
          if ($referecedEntityType === 'activity') {
            // This is a special case: We need to ensure that the
            // data type is defined in the json array and set
            // the basefield definitions to match the specific data type.
            // Verify the json.
            // Verify the decoded array.
            $decoded_json = json_decode($value, TRUE);
            if (empty($decoded_json)) {
              throw new ParserValidationException(WRONG_ENTITY_FORMAT, $this->line + 1, $this->column + 1);
            }
            // Validate array against field definitions.
            foreach ($decoded_json as $activityEntityType => $dataEntityValues) {
              if (empty($activityEntityType) || !is_string($activityEntityType)) {
                throw new ParserValidationException(WRONG_ENTITY_FORMAT, $this->line + 1, $this->column + 1);
              }
              $activityEntityTypeFieldDefinitions = $this->getFieldDefinitions('activity', $activityEntityType);
              if (!is_array($dataEntityValues)) {
                throw new ParserValidationException(WRONG_ENTITY_FORMAT, $this->line + 1, $this->column + 1, json_encode([array_keys($activityEntityTypeFieldDefinitions)]));
              }
              // Set data entity type.
              $dataEntityValues = [
                'type' => str_replace('field_', '', array_keys($dataEntityValues)[0]),
              ] + $dataEntityValues;
              if (array_keys($dataEntityValues) !== array_keys($activityEntityTypeFieldDefinitions)) {
                throw new ParserValidationException(WRONG_ENTITY_FORMAT, $this->line + 1, $this->column + 1, json_encode([array_keys($activityEntityTypeFieldDefinitions)]));
              }
            }
          }
          else {
            $referencedEntityFieldDefinitions = $this->getFieldDefinitions($referecedEntityType);
            // Verify the json.
            if (!preg_match('/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/', preg_replace('/"(\\.|[^"\\\\])*"/', '', $value))) {
              throw new ParserValidationException(WRONG_ENTITY_FORMAT, $this->line + 1, $this->column + 1, json_encode([array_keys($referencedEntityFieldDefinitions)]));
            }
            // Verify the decoded array.
            $decoded_json = json_decode($value, TRUE);
            if (!is_array($decoded_json) || empty($decoded_json)) {
              throw new ParserValidationException(WRONG_ENTITY_FORMAT, $this->line + 1, $this->column + 1, json_encode([array_keys($referencedEntityFieldDefinitions)]));
            }
            // Validate array against field definitions.
            foreach ($decoded_json as $entityValues) {
              if (!is_array($entityValues)) {
                throw new ParserValidationException(WRONG_ENTITY_FORMAT, $this->line + 1, $this->column + 1, json_encode([array_keys($referencedEntityFieldDefinitions)]));
              }
              if (array_keys($entityValues) !== array_keys($referencedEntityFieldDefinitions)) {
                throw new ParserValidationException(WRONG_ENTITY_FORMAT, $this->line + 1, $this->column + 1, json_encode([array_keys($referencedEntityFieldDefinitions)]));
              }
            }
          }
        }
        break;

      default:
    }
  }

  /**
   * Unpacks a json-formatted array into an entity.
   *
   * @param string $type
   *   The entity type to expect.
   * @param string $json
   *   A json-formatted string defining an entity.
   * @param bool $save
   *   Whether or not the entity should be saved.
   *
   * @return array
   *   The unpacked entity id(s).
   */
  private function unpackEntityReference($type, $json = NULL, $save = FALSE) {
    $ids = array();
    if (!empty($json)) {
      $array = json_decode($json, TRUE);
      try {
        switch ($type) {
          case 'tasks':
            $taskEntity = Task::create($array);
            if ($taskEntity && $save) {
              $taskEntity->save();
              $ids[] = $taskEntity->id();
              break;
            }
            break;

          case 'event_repeater':
            $eventRepeaterEntity = EventRepeater::create($array);
            if ($eventRepeaterEntity && $save) {
              $eventRepeaterEntity->save();
              $ids[] = $eventRepeaterEntity->id();
            }
            break;

          case 'participants':
            foreach ($array as $item) {
              $participantEntity = Participant::create($item);
              if ($participantEntity && $save) {
                $participantEntity->save();
                $ids[] = $participantEntity->id();
              }
            }
            break;

          case 'activities':
            foreach ($array as $activityType => $dataValues) {
              // Get data types and save corresponding data entities.
              $dataIds = [];
              foreach ($dataValues as $dataField => $dataValue) {
                $dataEntity = Data::create([
                  'type' => str_replace('field_', '', $dataField),
                  $dataField => $dataValue,
                ]);
                if ($dataEntity && $save) {
                  $dataEntity->save();
                  $dataIds[] = $dataEntity->id();
                }
              }
              $array['type'] = $activityType;
              $array[$dataField] = $dataIds;
              $activityEntity = Activity::create($array);
              if ($activityEntity && $save) {
                $activityEntity->save();
                $ids[] = $activityEntity->id();
              }
            }
            break;

          default:
        }
      }
      catch (EntityStorageException $exception) {
        drupal_set_message($exception->getMessage());
        drupal_set_message(t('The CSV file contains a row that failed to parse a value at line @line, column @column', ['@line' => $this->line, '@column' => $this->column]), 'error');
      }
    }
    return $ids;
  }

}
