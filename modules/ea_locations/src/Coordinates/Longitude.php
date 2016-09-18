<?php

namespace Drupal\ea_locations\Coordinates;

use Drupal\ea_locations\Controller\LocationController;
use Drupal\Core\TypedData\TypedData;

/**
 * The longitude of an address.
 */
class Longitude extends TypedData {

  /**
   * Cached value.
   *
   * @var string|null
   */
  protected $longitude = NULL;

  /**
   * {@inheritdoc}
   */
  public function getValue($langcode = NULL) {
    if ($this->longitude !== NULL) {
      return $this->longitude;
    }
    $item = $this->getParent();
    $locationController = new LocationController();
    $this->longitude = $locationController->getCoordinates($item->address);
    return $this->longitude;
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
        'lon',
      ))
      ->condition('address', $item->address)
      ->execute();
    $location = $result->fetchAssoc();
    if (!empty($location['lon'])) {
      $this->longitude = $location['lon'];
    }
    // Notify the parent of any changes.
    if ($notify & isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

}
