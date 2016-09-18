<?php

namespace Drupal\ea_locations\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'location' formatter.
 *
 * @FieldFormatter(
 *   id = "location_default",
 *   module = "ea_location",
 *   label = @Translation("Location"),
 *   field_types = {
 *     "location"
 *   }
 * )
 */
class LocationFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    foreach ($items as $delta => $item) {
      $elements[$delta] = array(
        '#markup' => $item->address,
      );
    }
    return $elements;
  }

}
