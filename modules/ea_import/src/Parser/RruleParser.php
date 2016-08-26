<?php
/**
 * @file
 * Contains \Drupal\ea_import\Parser\RruleParser.
 * 
 */

namespace Drupal\ea_import\Parser;

use Recurr\Rule;
use Recurr\Exception\InvalidRRule;

/**
 * Parses RRULEs.
 */
class RruleParser {

  /* Recurr\Rule object */
  private /** @type {Rule} */ $Rule;

  /* RRULE string */
  private /** @type {string} */ $rrule;

  /* timezone */
  private /** @type {DateTimeZone} */ $timezone;

  /* start date */
  private /** @type {DateTime} */ $startDate;

  /* end date */
  private /** @type {DateTime} */ $endDate;

  /**
   * Creates an RRULE Object.
   *
   * @param {string} $rrule An RRULE.
   *
   * @return Object The RruleParser Object.
   */
  public function __construct(string $rrule) {
    if (empty($rrule)) {
      return false;
    }
    $this->rrule = $rrule;
    return $this;
  }

  /**
   * Get values from the RRULE formatted for the EventRepeater entity.
   * 
   * @return string Values for an EventRepeater entity.
   */
  public function getEventRepeaterValues() {
    $eventRepeaterValues = array(
      'event_freq' => 'none',
      'event_count' => '1',
      'event_until' => date('Y-m-d'),
      'event_interval' => '1',
      'event_bymonth' => '1',
      'event_bymonthday' => '1',
      'event_byday' => 'MO',
      'event_bysetpos' => '1',
      'event_ends' => 'never',
      'event_byday_multiple' => '',
    );
    $components = explode(';', $this->rrule);
    foreach ($components as $component) {
      $componentArray = explode('=', $component);
      switch ($componentArray[0]) {
        case 'FREQ' :
          $eventRepeaterValues['event_freq'] = $componentArray[1];
          break;
        case 'COUNT' :
          $eventRepeaterValues['event_count'] = $componentArray[1];
          break;
        case 'UNTIL' :
          $eventRepeaterValues['event_until'] = $this->getDate($componentArray[1]);
          break;
        case 'INTERVAL' :
          $eventRepeaterValues['event_interval'] = $componentArray[1];
          break;
        case 'BYMONTH' :
          $eventRepeaterValues['event_bymonth'] = $componentArray[1];
          break;
        case 'BYMONTHDAY' :
          $eventRepeaterValues['event_bymonthday'] = $componentArray[1];
          break;
        case 'BYDAY' :
          // Assuming FREQ comes before BYDAY.
          if ($eventRepeaterValues['event_freq'] !== 'WEEKLY') {
            $eventRepeaterValues['event_byday'] = $componentArray[1];
          }
          else {
            $eventRepeaterValues['event_byday_multiple'] = $componentArray[1];
          }
          break;
        case 'BYSETPOS' :
          $eventRepeaterValues['event_bysetpos'] = $componentArray[1];
          break;
      }
    }
    return $eventRepeaterValues;
  }

  /**
   * Get the RRULE from the EventRepeater entity.
   * 
   * @param {EventRepeater Object} $frequency An EventRepeater entity.
   * 
   * @return string The RRULE corresponding to the EventRepeater entity.
   */
  public function setRRULE(EventRepeater $eventRepeater) {
    // Get variables.
    $frequency = $eventRepeater->event_frequency->value;
    $interval = $eventRepeater->event_interval->value;
    $ends = $eventRepeater->event_ends->value;
    $count = $eventRepeater->event_count->value;
    $until = $eventRepeater->event_until->value;
    $byday_multiple = implode(',', $eventRepeater->event_byday_multiple->value);
    $byday = $eventRepeater->event_byday->value;
    $event_repeat_by = $eventRepeater->event_repeat_by->value;
    $bymonthday = $eventRepeater->event_bymonthday->value;
    $bysetpos = $eventRepeater->event_bysetpos->value;
    $bymonth = $eventRepeater->event_bymonth->value;
    // Exit if event repeater is disabled.
    if ($frequency === 'none') {
      return NULL;
    }
    // Build RRULE.
    $rrule = "FREQ=$frequency;INTERVAL=$interval";
    switch ($frequency) {
      case 'WEEKLY' :
        $rrule .= ";BYDAY=$byday_multiple";
        break;
      case 'MONTHLY' :
        if ($event_repeat_by === 'day_of_the_month') {
          $rrule .= ";BYMONTHDAY=$bymonthday";
        }
        else {
          $rrule .= ";BYSETPOS=$bysetpos;BYDAY=$byday";
        }
        break;
      case 'YEARLY' :
        if ($event_repeat_by === 'day_of_the_month') {
          $rrule .= ";BYMONTHDAY=$bymonthday";
        }
        else {
          $rrule .= ";BYSETPOS=$bysetpos;BYDAY=$byday;BYMONTH=$bymonth";
        }
        break;
      case 'none' :
      case 'DAILY' :
      default :
    }
    // Add end configurations.
    switch ($ends) {
      case 'never' :
        break;
      case 'COUNT' :
        $rrule .= ";COUNT=$count";
        break;
      case 'UNTIL' :
        $rrule .= ";UNTIL=$until";
        break;
      default :
    }
    $this->rrule = $rrule;
    return $this;
  }

  /**
   * Format RRULE date to Drupal date.
   * 
   * @param {string} $date The RRULE date.
   * 
   * @return string The formatted date.
   */
  private function toDrupalDate(string $date) {
    $dateObject = DateTime::createFromFormat('Ymd\THis', $date);
    return $dateObject->format('Y-m-d');
  }

  /**
   * Format Drupal date to RRULE date.
   * 
   * @param {string} $date The Drupal date.
   * 
   * @return string The formatted date.
   */
  private function toRRULEDate(string $date) {
    $dateObject = DateTime::createFromFormat('Ymd\THis', $date);
    return $dateObject->format('Y-m-d');
  }
}
