<?php

/**
 * @file
 * Definition of Drupal\ea_locations\Plugin\Field\FieldFormatter\LocationFormatter.
 */

namespace Drupal\ea_locations\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal;

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
class LocationDefaultFormatter extends FormatterBase {
  
}
