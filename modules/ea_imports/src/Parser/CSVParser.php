<?php

namespace Drupal\ea_imports\Parser;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\file\Entity\File;

/**
 * Parses ICalendar.
 */
class CSVParser extends EntityParser implements ParserInterface {

  const BATCHSIZE = 50;

  const CSVHEADERFORMAT = array(
    'start_date',
    'end_date',
    'address',
    'address_extra_information',
    'title',
    'description',
    'participants',
    'tasks',
    'task_participants',
    'results',
  );

  /**
   * CSV filepath.
   *
   * @var string
   */
  private $filePath;

  /**
   * CSV file.
   *
   * @var resource
   */
  private $fileHandle;

  /**
   * Item count.
   *
   * @var int
   */
  private $itemCount;

  /**
   * Parent grouping.
   *
   * @var Grouping
   */
  private $grouping;

  /**
   * The current row number.
   *
   * @var int
   */
  private $row = 0;

  /**
   * The current column number.
   *
   * @var int
   */
  private $column = 0;

  /**
   * Tracks the latest read event.
   *
   * @var Event
   */
  private $latestEvent;

  /**
   * Tracks the latest read task.
   *
   * @var Task
   */
  private $latestTask;

  /**
   * Tracks the latest read result.
   *
   * @var Result
   */
  private $latestResult;

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
    $this->filePath = File::load($file)->getFileUri();
    $this->grouping = Grouping::load($gid);
    $this->setItemCount();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $isValid = TRUE;
    try {
      $this->fileHandle = fopen($this->filePath, "r");
      $this->validateHeader();
      $this->validateRows();
      fclose($this->fileHandle);
    }
    catch (ParserValidationException $exception) {
      $isValid = FALSE;
      switch ($exception->getErrorCode()) {
        case INVALID_HEADERS:
          $this->errorMessage = t('The CSV file does not contain valid column names. Column names should be "@column_names_format"', ['@column_names_format' => implode(',', self::CSVHEADERFORMAT)]);
          break;

        case INVALID_DATE:
          $this->errorMessage = t('The CSV file contains a row with an incorrect date at line @line, column @column.', ['@line' => $exception->getDataLine(), '@column' => $exception->getDataColumn()]);
          break;

        case INVALID_PARTICIPANT:
          $this->errorMessage = t('The CSV file contains a row with an incorrect participant at line @line, column @column.', ['@line' => $exception->getDataLine(), '@column' => $exception->getDataColumn()]);
          break;

        case INVALID_RESULT:
          $this->errorMessage = t('The CSV file contains a row with an incorrect result at line @line, column @column.', ['@line' => $exception->getDataLine(), '@column' => $exception->getDataColumn()]);
          break;

        case INVALID_DATA:
          $this->errorMessage = t('The CSV file contains a row with incorrect data at line @line, column @column.', ['@line' => $exception->getDataLine(), '@column' => $exception->getDataColumn()]);
          break;

        case INVALID_EVENT:
          $this->errorMessage = t('The CSV file contains a row with an incorrect event at line @line.', ['@line' => $exception->getDataLine()]);
          break;

        case WRONG_ROW_COUNT:
          $this->errorMessage = t('The CSV file contains a row with incorrect number of columns at line @line.', ['@line' => $exception->getDataLine()]);
          break;

        case PERMISSION_DENIED:
          $this->errorMessage = t('The CSV file contains a row with an inaccessable value at line @line, column @column.', ['@line' => $exception->getDataLine(), '@column' => $exception->getDataColumn()]);
          break;

      }
    }
    return $isValid;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrorMessage() {
    return $this->errorMessage;
  }

  /**
   * Validates the CSV header.
   */
  private function validateHeader() {
    $this->row++;
    if (fgetcsv($this->fileHandle) !== self::CSVHEADERFORMAT) {
      throw new ParserValidationException(INVALID_HEADERS, $this->row, $this->convertColumn($this->column));
    }
  }

  /**
   * Validates the CSV data.
   */
  private function validateRows() {
    while (($row = fgetcsv($this->fileHandle)) !== FALSE) {
      // Skip header.
      if ($this->row === 0) {
        $this->row++;
        continue;
      }
      $this->validateRow($row);
      $this->row++;
    }
  }

  /**
   * Validate row.
   *
   * @param array $row
   *   The row to validate.
   */
  private function validateRow($row) {
    foreach ($row as $column => $data) {
      $this->column = $column;
      switch (self::CSVHEADERFORMAT[$column]) {
        case 'start_date':
        case 'end_date':
          if (!empty($data)) {
            $date = \DateTime::createFromFormat('Y-m-d H:i', $data);
            if (!$date || $date->format('Y-m-d H:i') !== $data) {
              throw new ParserValidationException(INVALID_DATE, $this->row, $this->convertColumn($this->column));
            }
          }
          break;

        case 'tasks':
          if (!empty($data) && !$this->validateTask($data)) {
            throw new ParserValidationException(INVALID_TASK, $this->row, $this->convertColumn($this->column));
          }
          break;

        case 'task_participants':
        case 'participants':
          if (!empty($data) && (count(explode('|', $data)) !== 3 || !$this->validateParticipant(array_map('trim', explode('|', $data))))) {
            throw new ParserValidationException(INVALID_PARTICIPANT, $this->row, $this->convertColumn($this->column));
          }
          break;

        case 'results':
          $dataArray = array_map('trim', explode('|', $data));
          if (!empty($data) && (count(explode('|', $data)) < 5 || !$this->validateResult($dataArray, reset($dataArray), $this->grouping))) {
            throw new ParserValidationException(INVALID_RESULT, $this->row, $this->convertColumn($this->column));
          }
          break;

        case 'result_data':
          if (!empty($data) && (count(explode('|', $data)) !== 2 || !$this->validateData(array_map('trim', explode('|', $data)), reset(array_map('trim', explode('|', $data)))))) {
            throw new ParserValidationException(INVALID_DATA, $this->row, $this->convertColumn($this->column));
          }
          break;

      }
    }
    // Validate event if required fields are present.
    if ($this->isEvent($row)) {
      $values = [
        $row[array_search('title', self::CSVHEADERFORMAT)],
        $row[array_search('start_date', self::CSVHEADERFORMAT)],
        $row[array_search('end_date', self::CSVHEADERFORMAT)],
        [
          'address' => $row[array_search('address', self::CSVHEADERFORMAT)],
          'extra_information' => $row[array_search('address_extra_information', self::CSVHEADERFORMAT)],
        ],
        $row[array_search('description', self::CSVHEADERFORMAT)],
        NULL,
        NULL,
        NULL,
        $this->grouping->id(),
      ];
      if (!$this->validateEvent($values)) {
        throw new ParserValidationException(INVALID_EVENT, $this->row, NULL);
      }
    }
  }

  /**
   * Set the number of items to be imported.
   */
  private function setItemCount() {
    $this->itemCount = 0;
    $this->fileHandle = fopen($this->filePath, "r");
    while (fgetcsv($this->fileHandle) !== FALSE) {
      $this->itemCount++;
    }
    fclose($this->fileHandle);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItemCount() {
    return $this->itemCount;
  }

  /**
   * {@inheritdoc}
   */
  public function getNextBatch($position) {
    $this->fileHandle = fopen($this->filePath, "r");
    $this->row = 0;
    $itemCount = 0;
    $items = [];
    while (($row = fgetcsv($this->fileHandle)) !== FALSE) {
      // Skip to current row.
      if ($this->row === 0 || $this->row < $position) {
        $this->row++;
        continue;
      }
      $items[] = $row;
      $itemCount++;
      $this->row++;
      if ($itemCount === self::BATCHSIZE) {
        break;
      }
    }
    fclose($this->fileHandle);
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function importItem($values) {
    // Create event, if any.
    if ($this->isEvent($values)) {
      $participant = !empty($values[array_search('participants', self::CSVHEADERFORMAT)]) ? $this->importParticipant($this->getValue($values, 'participants')) : NULL;
      $participantId = !empty($participant) ? $participant->id() : NULL;
      // Create task, if any.
      $taskId = NULL;
      if (!empty($values[array_search('tasks', self::CSVHEADERFORMAT)])) {
        $this->latestTask = $this->importTask($values[array_search('tasks', self::CSVHEADERFORMAT)]);
        $taskId = !empty($this->latestTask) ? $this->latestTask->id() : NULL;
      }
      // Add task participants to latest task, if any.
      if (!empty($this->latestTask) && !empty($values[array_search('task_participants', self::CSVHEADERFORMAT)])) {
        $participant = $this->importParticipant($this->getValue($values, 'task_participants'));
        // Attach to latest task.
        $this->latestTask->participants[] = [
          'target_id' => $participant->id(),
        ];
        $this->latestTask->save();
      }
      // Create result, if any.
      $resultValues = $this->getValue($values, 'results');
      $result = !empty($values[array_search('results', self::CSVHEADERFORMAT)]) ? $this->importResult($resultValues, reset($resultValues), $this->grouping) : NULL;
      $resultId = !empty($result) ? $result->id() : NULL;
      // Create event.
      $this->latestEvent = $this->importEvent([
        $values[array_search('title', self::CSVHEADERFORMAT)],
        \DateTime::createFromFormat('Y-m-d H:i', $values[array_search('start_date', self::CSVHEADERFORMAT)])->format(DATETIME_DATETIME_STORAGE_FORMAT),
        \DateTime::createFromFormat('Y-m-d H:i', $values[array_search('end_date', self::CSVHEADERFORMAT)])->format(DATETIME_DATETIME_STORAGE_FORMAT),
        [
          'address' => $values[array_search('address', self::CSVHEADERFORMAT)],
          'extra_information' => $values[array_search('address_extra_information', self::CSVHEADERFORMAT)],
        ],
        $values[array_search('description', self::CSVHEADERFORMAT)],
        $taskId,
        $participantId,
        $resultId,
        $this->grouping->id(),
      ]);
    }
    // Otherwise, create and add extra entities.
    elseif (!empty($this->latestEvent)) {
      // Create participant, if any.
      if (!empty($values[array_search('participants', self::CSVHEADERFORMAT)]) && !empty($this->latestEvent)) {
        $entity = $this->importParticipant($this->getValue($values, 'participants'));
        if ($entity) {
          // Attach to latest event.
          $this->latestEvent->participants[] = [
            'target_id' => $entity->id(),
          ];
          $this->latestEvent->save();
        }
      }
      // Create task, if any.
      if (!empty($values[array_search('tasks', self::CSVHEADERFORMAT)]) && !empty($this->latestEvent)) {
        $this->latestTask = $this->importTask(reset($this->getValue($values, 'tasks')));
        if ($this->latestTask) {
          // Attach to latest event.
          $this->latestEvent->tasks[] = [
            'target_id' => $this->latestTask->id(),
          ];
          $this->latestEvent->save();
        }
      }
      // Create task participant, if any.
      if (!empty($values[array_search('task_participants', self::CSVHEADERFORMAT)]) && !empty($this->latestTask)) {
        $entity = $this->importParticipant($this->getValue($values, 'task_participants'));
        if ($entity) {
          // Attach to latest task.
          $this->latestTask->participants[] = [
            'target_id' => $entity->id(),
          ];
          $this->latestTask->save();
        }
      }
      // Create result, if any.
      if (!empty($values[array_search('results', self::CSVHEADERFORMAT)]) && !empty($this->latestEvent)) {
        $resultValues = $this->getValue($values, 'results');
        $entity = $this->importResult($resultValues, reset($resultValues), $this->grouping);
        if ($entity) {
          // Attach to latest event.
          $this->latestEvent->results[] = [
            'target_id' => $entity->id(),
          ];
          $this->latestEvent->save();
        }
      }
    }
    return $this->latestEvent->id();
  }

  /**
   * Checks if row contains an event.
   *
   * @param array $row
   *   The row to check.
   *
   * @return bool
   *   Whether or not the row contains an event.
   */
  private function isEvent($row) {
    return !empty($row[array_search('start_date', self::CSVHEADERFORMAT)]);
  }

  /**
   * Returns trimmed values from the corresponding column.
   *
   * @param array $row
   *   The row to search value in.
   * @param string $columnName
   *   The column name.
   *
   * @return array
   *   Return values.
   */
  private function getValue($row, $columnName) {
    return array_map('trim', explode('|', $row[array_search($columnName, self::CSVHEADERFORMAT)]));
  }

  /**
   * Converts a column number to Excel-format column.
   *
   * @param int $column
   *   The column number to convert.
   *
   * @return string
   *   The corresponding column name.
   */
  private function convertColumn($column) {
    for ($name = ""; $column >= 0; $column = intval($column / 26) - 1) {
      $name = chr($column % 26 + 0x41) . $name;
    }
    return $name;
  }

}
