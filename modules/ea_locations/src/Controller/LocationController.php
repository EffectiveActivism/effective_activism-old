<?php

namespace Drupal\ea_locations\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal;

/**
 * Returns autocomplete responses for locations.
 */
class LocationController {

  const AUTOCOMPLETE_URL = 'https://maps.googleapis.com/maps/api/place/autocomplete';
  const GEOCODE_URL = 'https://maps.googleapis.com/maps/api/geocode';
  const LOCATION_CACHE_TABLE = 'ea_locations_addresses';

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
    $suggestions = $this->getAddressSuggestions($string);
    foreach ($suggestions as $suggestion) {
      $autocomplete_suggestions[] = array('value' => $suggestion, 'label' => $suggestion);
    }
    return new JsonResponse($autocomplete_suggestions);
  }

  /**
   * Validates an address.
   *
   * @param string $address
   *   An address string that needs to be validated against Google Maps API
   *   format.
   *
   * @return bool
   *   Returns TRUE if string is a valid Google Maps address, FALSE if not.
   *   If any connection errors occur, validation returns TRUE.
   */
  public function validateAddress($address) {
    $match = FALSE;
    // First check cache.
    $database = \Drupal::database();
    $results = $database->select(self::LOCATION_CACHE_TABLE, 'location')
      ->fields('location', ['id'])
      ->condition('location.address', $address)
      ->countQuery()
      ->execute()
      ->fetchField();
    if ($results > 0) {
      $match = TRUE;
    }
    else {
      $valid_addresses = $this->getAddressSuggestions($address);
      if (in_array($address, $valid_addresses)) {
        // Get coordinates and store valid address in cache.
        $coordinates = $this->getCoordinates($address);
        $database->insert(self::LOCATION_CACHE_TABLE)->fields(array(
          'address' => $address,
          'lat' => $coordinates['lat'],
          'lon' => $coordinates['lon'],
        ))->execute();
        $match = TRUE;
      }
    }
    return $match;
  }

  /**
   * Get locations based on input.
   *
   * @param string $input
   *   A location string.
   *
   * @return array
   *   Returns array of suggestions or FALSE if any connection errors occur.
   */
  public function getAddressSuggestions($input) {
    $suggestions = FALSE;
    if (!empty($input)) {
      $query = array(
        'input' => $input,
        'language' => $language = Drupal::languageManager()->getCurrentLanguage()->getName(),
      );
      $json = $this->request(self::AUTOCOMPLETE_URL, $query);
      if (isset($json->status) && $json->status === 'OK' && !empty($json->predictions)) {
        $suggestions = array();
        foreach ($json->predictions as $prediction) {
          $suggestions[] = $prediction->description;
        }
      }
    }
    return $suggestions;
  }

  /**
   * Get coordinates based off an address.
   *
   * @param string $address
   *   A Google address string.
   *
   * @return array
   *   Returns array of coordinates as array('lat' => float, 'lon' => float)
   *   or FALSE if no coordinates are found.
   */
  public function getCoordinates($address) {
    $coordinates = [
      'lat' => '',
      'lon' => '',
    ];
    if (!empty($address)) {
      $query = array(
        'address' => $address,
      );
      $json = $this->request(self::GEOCODE_URL, $query);
      if (
        !empty($json->status) &&
        $json->status === 'OK' &&
        !empty($json->results[0]->geometry->location->lat) &&
        !empty($json->results[0]->geometry->location->lng)
      ) {
        $coordinates['lat'] = $json->results[0]->geometry->location->lat;
        $coordinates['lon'] = $json->results[0]->geometry->location->lng;
      }
    }
    return $coordinates;
  }

  /**
   * Make a request to Google APIs.
   *
   * @param string $url
   *   A Google API url.
   * @param array $query
   *   The query to submit.
   *
   * @return string
   *   Returns JSON response.
   */
  private function request($url, array $query) {
    // Set Google API key.
    $query['key'] = Drupal::config('ea_locations.settings')->get('key');
    if (!empty($query['key'])) {
      try {
        $request = Drupal::httpClient()->get($url . '/json?' . http_build_query($query));
        $response = $request->getBody()->getContents();
        if (!empty($response)) {
          return json_decode($response);
        }
      }
      catch (BadResponseException $exception) {
        return FALSE;
      }
      catch (RequestException $exception) {
        return FALSE;
      }
    }
    return FALSE;
  }

}
