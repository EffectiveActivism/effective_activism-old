<?php

namespace Drupal\ea_locations\Coordinates;

use Drupal\Core\TypedData\TypedData;

/**
 * The latitude of an address.
 */
class Latitude extends TypedData {

  /**
   * Cached value.
   *
   * @var float|null
   */
  protected $latitude = NULL;

  /**
   * {@inheritdoc}
   */
  public function getValue($langcode = NULL) {
    return $this->latitude;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    $this->latitude = $value;
    // Notify the parent of any changes.
    if ($notify & isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

}
