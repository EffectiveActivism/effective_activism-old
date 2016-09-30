<?php

/**
 * @file
 * Contains result.page.inc..
 *
 * Page callback for Result entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Result templates.
 *
 * Default template: result.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_result(array &$variables) {
  // Fetch Result Entity Object.
  $result = $variables['elements']['#result'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

/**
 * Prepares variables for a custom entity type creation list templates.
 *
 * Default template: result-content-add-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - content: An array of result-types.
 *
 * @see block_content_add_page()
 */
function template_preprocess_result_content_add_list(&$variables) {
  $variables['types'] = array();
  $query = \Drupal::request()->query->all();
  foreach ($variables['content'] as $type) {
    $variables['types'][$type->id()] = array(
      'link' => Link::fromTextAndUrl($type->id(), new Url('entity.result.add_form', array(
        'result_type' => $type->id(),
      ), array('query' => $query))),
      'description' => array(
        '#markup' => $type->id(),
      ),
      'title' => $type->id(),
      'localized_options' => array(
        'query' => $query,
      ),
    );
  }
}
