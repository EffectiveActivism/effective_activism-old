<?php

namespace Drupal\ea_imports\Parser;

/**
 * Provides an interface for defining Parser objects.
 *
 * @ingroup ea_imports
 */
interface ParserInterface {

  /**
   * Validates data against object format.
   *
   * @return bool
   *   Whether or not data is valid.
   */
  public function validate();

  /**
   * Imports parsed data.
   *
   * @return bool
   *   Whether or not import was successful.
   */
  public function import();

  /**
   * Returns a translated error message, if any.
   *
   * @return string
   *   The error message.
   */
  public function getErrorMessage();

}
