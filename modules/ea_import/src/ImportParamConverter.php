<?php

/**
 * @file
 * Contains \Drupal\ea_import\ImportParamConverter.
 */

namespace Drupal\ea_import;

use \Drupal\ea_import\Storage\ICalendarStorage;
use \Drupal\Core\ParamConverter\ParamConverterInterface;
use \Symfony\Component\Routing\Route;

class ImportParamConverter implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if (!empty($value)) {
      $results = ICalendarStorage::load(array('iid' => $value));
      if (!empty($results)) {
        return $results[0];
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] === 'ea_icalendar');
  }
}
