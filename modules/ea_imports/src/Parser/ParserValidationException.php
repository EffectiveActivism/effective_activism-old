<?php

namespace Drupal\ea_imports\Parser;

use \Exception;

const WRONG_COLUMN_COUNT = -1;

const INVALID_HEADERS = -2;

const REQUIRED_VALUE = -3;

const WRONG_INT_FORMAT = -4;

const WRONG_DATE_FORMAT = -5;

const WRONG_LOCATION_FORMAT = -6;

const WRONG_ENTITY_FORMAT = -7;

const PERMISSION_DENIED = -8;

/**
 * Exception for parser validation errors.
 */
class ParserValidationException extends Exception {

  /**
   * Data line number.
   * 
   * @var int
   */
  private $dataLine;

  /**
   * Data column number.
   * 
   * @var int
   */
  private $dataColumn;

  /**
   * Additional information.
   * 
   * @var string
   */
  private $additionalInformation;

  /**
   * Constructs a ParserValidationException.
   * 
   * @param string $message
   *   The exception message.
   * @param int $line
   *   The line of the data file where the exception was thrown.
   * @param int $column
   *   The column of the data file where the exception was thrown.
   * @param string $additionalInformation
   *   Any extra information that needs to be passed as a variable.
   */
  public function __construct($message, int $line = NULL, int $column = NULL, $additionalInformation = NULL) {
    $this->dataLine = $line;
    $this->dataColumn = $column;
    $this->additionalInformation = $additionalInformation;
    parent::__construct($message);
  }

  /**
   * Set the current line of the data file.
   * 
   * @param int dataLine
   *   The current line of the file.
   */
  public function setDataLine(int $dataLine) {
    $this->dataLine = $dataLine;
    return $this;
  }

  /**
   * Returns the line of the data file where the exception was registered.
   * 
   * @return int
   *   The line number.
   */
  public function getDataLine() {
    return $this->dataLine;
  }

  /**
   * Set the current column of the data file.
   * 
   * @param int dataColumn
   *   The current column of the file.
   */
  public function setDataColumn(int $dataColumn) {
    $this->dataColumn = $dataColumn;
    return $this;
  }

  /**
   * Returns the column of the data file where the exception was registered.
   * 
   * @return int
   *   The column number.
   */
  public function getDataColumn() {
    return $this->dataColumn;
  }

  /**
   * Set the additional information.
   * 
   * @param int additionalInformation
   *   The current column of the file.
   */
  public function setAdditionalInformation($additionalInformation) {
    $this->additionalInformation = $additionalInformation;
    return $this;
  }

  /**
   * Returns the additional information.
   * 
   * @return string
   *   The additional information.
   */
  public function getAdditionalInformation() {
    return $this->additionalInformation;
  }

}
