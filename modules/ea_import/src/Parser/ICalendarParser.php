<?php
/**
 * @file
 * Contains \Drupal\ea_import\Form\ICalendarParser.
 * 
 * Rewritten from https://github.com/MartinThoma/ics-parser/
 */

namespace Drupal\ea_import\Parser;

/**
 * Parses ICalendar.
 */
class ICalendarParser {

  /* The raw calendar. */
  private /** @type {string} */ $raw;

  /* The parsed calendar. */
  private /** @type {Array} */ $cal;

  /* How many events are in this iCal? */
  private /** @type {int} */ $event_count = 0;

  /* Which keyword has been added to cal at last? */
  private /** @type {string} */ $last_keyword;

  /* Filters to apply */
  private /** @type {Array} */ $filters;

  /**
   * Creates the ICalendarParser Object.
   *
   * @param {mixed} $lines An array of lines from an iCal file.
   *
   * @return Object The iCal Object.
   */
  public function __construct(string $url, array $filters) {
    if (empty($url)) {
      return false;
    }
    // Convert webcal scheme to http, as Guzzler may not support webcal.
    $count = 1;
    $url = strpos($url, 'webcal://') === 0 ? str_replace('webcal://', 'http://', $url, $count) : $url;
    // Retrieve url.
    $client = \Drupal::httpClient();
    $request = $client->get($url);
    $this->raw = (string) $request->getBody();
    $this->filters = $filters;
    $lines = explode("\n", $this->raw);
    $this->initLines($lines);
    return $this;
  }

  /**
   * Return parsed events.
   * 
   * @return Array List of event data.
   */
  public function getEvents() {
    $events = [];
    if (isset($this->cal['VEVENT']) && !empty($this->cal['VEVENT'])) {
      foreach ($this->cal['VEVENT'] as $event) {
        // Apply filters, if any.
        if ((
          // If title doesn't contain string from title filter.
          !empty($this->filters['title']) &&
          !empty($event['SUMMARY']) &&
          strstr($event['SUMMARY'], $this->filters['title']) === FALSE
        ) ||
        (
          // Or description doesn't contain string from description filter.
          !empty($this->filters['description']) &&
          !empty($event['DESCRIPTION']) &&
          strstr($event['DESCRIPTION'], $this->filters['description']) === FALSE
        ) ||
        (
          // Or start date is older than start date filter.
          !empty($this->filters['date_start']) &&
          !empty($event['DSTART']) &&
          strtotime($this->filters['date_start']) < strtotime($event['DTSTART'])
        ) ||
        (
          // Or end date is newer than end date filter.
          !empty($this->filters['date_end']) &&
          !empty($event['DEND']) &&
          strtotime($this->filters['date_end']) > strtotime($event['DEND'])
        )) {
          // ... Then skip event.
          continue;
        }
        // Create event.
        $events[] = array(
          'title' => !empty($event['SUMMARY']) ? $event['SUMMARY'] : NULL,
          'description' => !empty($event['DESCRIPTION']) ? str_replace(['\n', '\,'], ["\n"], $event['DESCRIPTION']) : NULL,
          'start_date' => !empty($event['DTSTART']) ? date(DATETIME_DATETIME_STORAGE_FORMAT, (int) strtotime($event['DTSTART'])) : NULL,
          'end_date' => !empty($event['DTEND']) ? date(DATETIME_DATETIME_STORAGE_FORMAT, (int)  strtotime($event['DTEND'])) : NULL,
          'location' => !empty($event['LOCATION']) ? $event['LOCATION'] : NULL,
          'uid' => !empty($event['UID']) ? $event['UID'] : NULL,
        );
      }
    }
    return $events;
  }

  /**
   * Validate headers.
   * 
   * @return Boolean Whether or not ICalendar headers are valid.
   */
  public function validateHeaders() {
    return preg_match("
      /BEGIN:VCALENDAR.*VERSION:[12]\.0.*END:VCALENDAR/s", $this->raw);
  }

  /**
   * Initializes lines from file.
   *
   * @param {array} $lines The lines to initialize.
   *
   * @return Object The iCal Object.
   */
  private function initLines($lines) {
    if (stristr($lines[0], 'BEGIN:VCALENDAR') === false) {
      return false;
    }
    else {
      foreach ($lines as $line) {
        $line = rtrim($line); // Trim trailing whitespace
        $add  = $this->keyValueFromString($line);
        if ($add === false) {
          $this->addCalendarComponentWithKeyAndValue($component, false, $line);
          continue;
        }
        $keyword = $add[0];
        $values = $add[1]; // Could be an array containing multiple values.
        if (!is_array($values)) {
          if (!empty($values)) {
            $values = array($values); // Make an array as not already.
            $blank_array = array(); // Empty placeholder array.
            array_push($values, $blank_array);
          } else {
            $values = array(); // Use blank array to ignore this line.
          }
        } else if (empty($values[0])) {
          $values = array(); // Use blank array to ignore this line.
        }
        $values = array_reverse($values); // Reverse so that our array of properties is processed first.
        foreach ($values as $value) {
          switch ($line) {
            // http://www.kanzaki.com/docs/ical/vevent.html
            case 'BEGIN:VEVENT':
              if (!is_array($value)) {
                  $this->event_count++;
              }
              $component = 'VEVENT';
              break;
            // All other special strings
            case 'BEGIN:VCALENDAR':
            case 'BEGIN:DAYLIGHT':
            // http://www.kanzaki.com/docs/ical/vtimezone.html
            case 'BEGIN:VTIMEZONE':
            case 'BEGIN:STANDARD':
              $component = $value;
              break;
            case 'END:VEVENT':
            case 'END:VCALENDAR':
            case 'END:DAYLIGHT':
            case 'END:VTIMEZONE':
            case 'END:STANDARD':
              $component = 'VCALENDAR';
              break;
            default:
              $this->addCalendarComponentWithKeyAndValue($component, $keyword, $value);
              break;
          }
        }
      }
    }
  }

  /**
   * Add to $this->ical array one value and key.
   *
   * @param {string} $component This could be VTODO, VEVENT, VCALENDAR, ...
   * @param {string} $keyword   The keyword, for example DTSTART.
   * @param {string} $value     The value, for example 20110105T090000Z.
   *
   * @return {None}
   */
  private function addCalendarComponentWithKeyAndValue($component, $keyword, $value) {
    if ($keyword == false) {
      $keyword = $this->last_keyword;
    }
    switch ($component) {
      case 'VEVENT':
        if (!isset($this->cal[$component][$this->event_count - 1][$keyword . '_array'])) {
          $this->cal[$component][$this->event_count - 1][$keyword . '_array'] = array(); // Create array().
        }
        if (is_array($value)) {
          array_push($this->cal[$component][$this->event_count - 1][$keyword . '_array'], $value); // Add array of properties to the end.
        } else {
          if (!isset($this->cal[$component][$this->event_count - 1][$keyword])) {
            $this->cal[$component][$this->event_count - 1][$keyword] = $value;
          }
          $this->cal[$component][$this->event_count - 1][$keyword . '_array'][] = $value;
          // Glue back together for multi-line content.
          if ($this->cal[$component][$this->event_count - 1][$keyword] != $value) {
            $ord = (isset($value[0])) ? ord($value[0]) : NULL; // First char.

            if (in_array($ord, array(9, 32))) { // Is space or tab?.
                $value = substr($value, 1); // Only trim the first character.
            }
            if (is_array($this->cal[$component][$this->event_count - 1][$keyword . '_array'][1])) { // Account for multiple definitions of current keyword (e.g. ATTENDEE).
                $this->cal[$component][$this->event_count - 1][$keyword] .= ';' . $value; // Concat value *with separator* as content spans multiple lines.
            } else {
              if ($keyword === 'EXDATE') {
                // This will give out a comma separated EXDATE string as per RFC2445.
                // Example: EXDATE:19960402T010000Z,19960403T010000Z,19960404T010000Z.
                // Usage: $event['EXDATE'] will print out 19960402T010000Z,19960403T010000Z,19960404T010000Z.
                $this->cal[$component][$this->event_count - 1][$keyword] .= ',' . $value;
              } else {
                // Concat value as content spans multiple lines
                $this->cal[$component][$this->event_count - 1][$keyword] .= $value;
              }
            }
          }
        }
        break;
      default:
        $this->cal[$component][$keyword] = $value;
        break;
    }
    $this->last_keyword = $keyword;
  }

  /**
   * Get a key-value pair of a string.
   *
   * @param {string} $text which is like "VCALENDAR:Begin" or "LOCATION:"
   *
   * @return {array} array("VCALENDAR", "Begin").
   */
  private function keyValueFromString($text) {
    // Match colon separator outside of quoted substrings.
    // Fallback to nearest semicolon outside of quoted substrings, if colon cannot be found.
    // Do not try and match within the value paired with the keyword.
    preg_match('/(.*?)(?::(?=(?:[^"]*"[^"]*")*[^"]*$)|;(?=[^:]*$))([\w\W]*)/', htmlspecialchars($text, ENT_QUOTES, 'UTF-8'), $matches);
    if (count($matches) == 0) {
      return false;
    }
    if (preg_match('/^([A-Z-]+)([;][\w\W]*)?$/', $matches[1])) {
      $matches = array_splice($matches, 1, 2); // Remove first match and re-align ordering.
      // Process properties
      if (preg_match('/([A-Z-]+)[;]([\w\W]*)/', $matches[0], $properties)) {
        array_shift($properties); // Remove first match.
        $matches[0] = $properties[0]; // Fix to ignore everything in keyword after a ; (e.g. Language, TZID, etc.).
        array_shift($properties); // Repeat removing first match.
        $formatted = array();
        foreach ($properties as $property) {
          preg_match_all('~[^\r\n";]+(?:"[^"\\\]*(?:\\\.[^"\\\]*)*"[^\r\n";]*)*~', $property, $attributes); // Match semicolon separator outside of quoted substrings.
          $attributes = (sizeof($attributes) == 0) ? array($property) : reset($attributes); // Remove multi-dimensional array and use the first key.
          foreach ($attributes as $attribute) {
            preg_match_all('~[^\r\n"=]+(?:"[^"\\\]*(?:\\\.[^"\\\]*)*"[^\r\n"=]*)*~', $attribute, $values); // Match equals sign separator outside of quoted substrings.
            $value = (sizeof($values) == 0) ? NULL : reset($values); // Remove multi-dimensional array and use the first key.
            if (is_array($value) && isset($value[1])) {
              $formatted[$value[0]] = trim($value[1], '"'); // Remove double quotes from beginning and end only.
            }
          }
        }
        $properties[0] = $formatted; // Assign the keyword property information.
        array_unshift($properties, $matches[1]); // Add match to beginning of array.
        $matches[1] = $properties;
      }
      return $matches;
    } else {
      return false; // Ignore this match.
    }
  }
}
