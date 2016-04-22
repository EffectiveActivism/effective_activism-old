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
    $autocomplete_suggestions = array();
    $string = $request->query->get('q');
    $suggestions = LocationController::getLocations($string);
    foreach ($suggestions as $suggestion) {
      $autocomplete_suggestions[] = array('value' => $suggestion, 'label' => $suggestion);
    }
    return new JsonResponse($autocomplete_suggestions);
  }

  /**
   * Validates a location.
   *
   * @param String $location
   *   A location string that needs to be validated against Google Maps API
   *   format.
   *
   * @return Boolean
   *   Returns TRUE if string is a valid Google Maps address, FALSE if not.
   *   If any connection errors occur, validation returns TRUE.
   */
  public function validateLocation($location) {
    $valid_locations = LocationController::getLocations($location);
    if ($valid_locations) {
      return in_array($location, $valid_locations);
    }
    else {
      return TRUE;
    }
  }

  /*
   * Get locations based on input.
   *
   * @param String $input
   *   A location string.
   *
   * @return Array
   *   Returns array of suggestions or FALSE if any connection errors occur.
   */
  public function getLocations($input) {
    $suggestions = FALSE;
    if (!empty($input)) {
      $language = Drupal::languageManager()->getCurrentLanguage();
      $config = Drupal::config('ea_locations.settings');
      $query = array(
        'input' => $input,
        'language' => $language->getName(),
        'key' => $config->get('key'),
      );
      if (!empty($query['key'])) {
        try {
          $request = Drupal::httpClient()->get('https://maps.googleapis.com/maps/api/place/autocomplete/json?' . http_build_query($query));
          $response = $request->getBody()->getContents();
          if (!empty($response)) {
            $json = json_decode($response);
            if (isset($json->status) && $json->status == 'OK' && !empty($json->predictions)) {
              $suggestions = array();
              foreach ($json->predictions as $prediction) {
                $suggestions[] = $prediction->description;
              }
            }
          }
        }
        catch (BadResponseException $exception) {
          $suggestions = FALSE;
        }
        catch (RequestException $exception) {
          $suggestions = FALSE;
        }
      }
    }
    return $suggestions;
  }
}
