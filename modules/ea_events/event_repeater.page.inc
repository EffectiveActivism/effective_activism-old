<?php

/**
 * @file
 * Contains event_repeater.page.inc.
 *
 * Page callback for Event repeater entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Event repeater templates.
 *
 * Default template: event_repeater.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_event_repeater(array &$variables) {
  // Fetch EventRepeater Entity Object.
  $event_repeater = $variables['elements']['#event_repeater'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
