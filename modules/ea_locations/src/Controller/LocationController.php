<?php

/**
 * @file
 * Contains \Drupal\ea_locations\Controller\LocationController.
 */

namespace Drupal\ea_locations\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal;

/**
 * Returns autocomplete responses for locations.
 */
class LocationController {

  /**
   * Returns response for the location autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for countries.
   */
  public function autocomplete(Request $request) {
    $suggestions = array('none');
    $string = $request->query->get('q');
    if ($string) {
      $language = \Drupal::languageManager()->getCurrentLanguage();
      $query = array(
        'input' => $string,
        'language' => $language->getName(),
        'key' => '',
      );
      $uri = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?' . http_build_query($query);
      $request = \Drupal::httpClient()->createRequest('GET', $uri);
      $response = $client->send($request);
      $json = $response->json();
      if (!empty($json)) {
        $predictions = json_decode($json);
        if (isset($predictions->Status) && $predictions->Status == 'OK' && !empty($predictions['predictions'])) {
          foreach ($predictions['predictions'] as $prediction) {
            $suggestions[] = $prediction->description;
          }
        }
      }
    }
    return new JsonResponse(array($suggestions));
  }
}
