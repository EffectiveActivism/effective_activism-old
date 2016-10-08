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
   * Get the number of items to be imported.
   *
   * @return int
   *   The number of items to import.
   */
  public function getCount();

  /**
   * Get the items to be imported.
   *
   * @param int $currentItem
   *   The item number to start import from.
   *
   * @return array
   *   The items to import.
   */
  public function getItems($currentItem);

  /**
   * Imports parsed items.
   *
   * @param array $values
   *   The values to map to an entity.
   *
   * @return bool
   *   Whether the import was successful or not.
   */
  public function import($values);

  /**
   * Returns a translated error message, if any.
   *
   * @return string
   *   The error message.
   */
  public function getErrorMessage();

}
