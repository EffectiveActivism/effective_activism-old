<?php

namespace Drupal\ea_imports\Parser;

use \Exception;

const INVALID_HEADERS = -1;

const INVALID_DATE = -2;

const INVALID_PARTICIPANT = -3;

const INVALID_RRULE = -4;

const INVALID_RESULT = -5;

const INVALID_DATA = -6;

const INVALID_EVENT = -7;

const WRONG_ROW_COUNT = -8;

const PERMISSION_DENIED = -9;

/**
 * Exception for parser validation errors.
 */
class ParserValidationException extends Exception {

  /**
   * Error code.
   *
   * @var int
   */
  private $errorCode;

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
   * Constructs a ParserValidationException.
   *
   * @param int $errorCode
   *   The exception error code.
   * @param int $line
   *   The line of the data file where the exception was thrown.
   * @param int $column
   *   The column of the data file where the exception was thrown.
   * @param string $additionalInformation
   *   Any extra information that needs to be passed as a variable.
   */
  public function __construct($errorCode, $line = NULL, $column = NULL) {
    $this->errorCode = $errorCode;
    $this->dataLine = $line;
    $this->dataColumn = $column;
    parent::__construct();
  }

  /**
   * Returns the error code.
   *
   * @return int
   *   The error code.
   */
  public function getErrorCode() {
    return $this->errorCode;
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
   * Returns the column of the data file where the exception was registered.
   *
   * @return int
   *   The column number.
   */
  public function getDataColumn() {
    return $this->dataColumn;
  }

}
