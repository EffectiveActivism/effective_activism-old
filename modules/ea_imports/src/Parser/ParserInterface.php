<?php

namespace Drupal\ea_imports\Parser;

/**
 * Provides an interface for defining Parser objects.
 *
 * @ingroup ea_imports
 */
interface ParserInterface {

  /**
   * Get the number of items to be imported.
   *
   * @return int
   *   The number of items to import.
   */
  public function getItemCount();

  /**
   * Get the items to be imported.
   *
   * @param array $position
   *   The position to start from.
   *
   * @return array
   *   The items to import.
   */
  public function getNextBatch(array $position);

  /**
   * Imports parsed items.
   *
   * @param array $values
   *   The values to map to an entity.
   *
   * @return int|bool
   *   Returns item entity id or FALSE if import failed.
   */
  public function importItem(array $values);

  /**
   * Validates items.
   *
   * @return bool
   *   Whether the items are valid or not.
   */
  public function validate();

  /**
   * Returns a validation error message, if any.
   *
   * @return string|null
   *   The validation error message.
   */
  public function getErrorMessage();

}
