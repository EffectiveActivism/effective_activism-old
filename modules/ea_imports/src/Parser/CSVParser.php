<?php

namespace Drupal\ea_imports\Parser;

use Drupal\file\Entity\File;

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
   * Creates the CSVParser Object.
   *
   * @param string $file
   *   An iCal file.
   */
  public function __construct($file) {
    if (empty($file)) {
      return FALSE;
    }
    // Load file entity.
    $csv_file = File::load($file);
    $this->file = $csv_file;
    return $this;
  }

  /**
   * Return parsed events.
   *
   * @return array
   *   List of event data.
   */
  public function getEvents() {
    $events = [];
    if (($handle = fopen($this->file->getFileUri(), "r")) !== FALSE) {
      $row = 0;
      while (($data = fgetcsv($handle)) !== FALSE) {
        $row++;
        // Skip headers.
        if ($row === 1) {
          continue;
        }
        $events[] = array(
          'title' => $data[0],
          'description' => $data[1],
          'start_date' => date(DATETIME_DATETIME_STORAGE_FORMAT, $data[2]),
          'end_date' => date(DATETIME_DATETIME_STORAGE_FORMAT, $data[3]),
          'location' => $data[4],
        );
      }
      fclose($handle);
    }
    return $events;
  }

  /**
   * Validate headers.
   *
   * @return bool
   *   Whether or not CSV headers are valid.
   */
  public function validateHeaders() {
    $isValid = FALSE;
    if (($handle = fopen($this->file->getFileUri(), "r")) !== FALSE) {
      $titles = fgetcsv($handle);
      fclose($handle);
      if (
        count($titles) < 6 ||
        empty($titles) ||
        $titles[0] !== 'title' ||
        $titles[1] !== 'description' ||
        $titles[2] !== 'date_start' ||
        $titles[3] !== 'date_end' ||
        $titles[4] !== 'location' ||
        $titles[5] !== 'location_title'
      ) {
        $isValid = FALSE;
      }
      else {
        $isValid = TRUE;
      }
    }
    return $isValid;
  }

}
