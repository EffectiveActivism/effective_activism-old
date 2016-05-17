<?php

/**
 * @file
 * Contains grouping.page.inc.
 *
 * Page callback for Grouping entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Grouping templates.
 *
 * Default template: grouping.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_grouping(array &$variables) {
  // Fetch Grouping Entity Object.
  $grouping = $variables['elements']['#grouping'];
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
