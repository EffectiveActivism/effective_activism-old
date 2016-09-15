<?php

/**
 * @file
 * Contains \Drupal\ea_locations\Coordinates\Latitude.
 */

namespace Drupal\ea_locations\Coordinates;

use Drupal\ea_locations\Controller\LocationController;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedData;

/**
 * The latitude of an address.
 */
class Latitude extends TypedData {

  /**
   * Cached value.
   *
   * @var string|null
   */
  protected $latitude = NULL;

  /**
   * {@inheritdoc}
   */
  public function getValue($langcode = NULL) {
    if ($this->latitude !== NULL) {
      return $this->latitude;
    }
    $item = $this->getParent();
    $locationController = new LocationController;
    $this->latitude = $locationController->getCoordinates($item->address);
    return $this->latitude;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    $item = $this->getParent();
    // First check if the cache table has the value.
    $database = \Drupal::database();
    $result = $database->select('ea_locations_addresses', 'a')
      ->fields('a', array(
        'lat',
      ))
      ->condition('address', $item->address)
      ->execute();
    $location = $result->fetchAssoc();
    if (!empty($location['lat'])) {
      $this->longitude = $location['lat'];
    }
    // Notify the parent of any changes.
    if ($notify & isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }
}
