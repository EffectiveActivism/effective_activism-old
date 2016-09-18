<?php

namespace Drupal\ea_import\Parser;

use Drupal\ea_events\Entity\EventRepeater;

/**
 * Parses RRULEs.
 */
class RruleParser {

  /* Recurr\Rule object */
  private /* @type Rule */ $Rule;

  /* RRULE string */
  private /* @type string */ $rrule;

  /* timezone */
  private /* @type DateTimeZone */ $timezone;

  /* start date */
  private /* @type DateTime */ $startDate;

  /* end date */
  private /* @type DateTime */ $endDate;

  /* array of values */
  private /* @type array */$eventRepeaterValues;

  /**
   * Creates an RRULE Object.
   *
   * @param string $rrule
   *   An RRULE.
   */
  public function __construct($rrule) {
    if (empty($rrule)) {
      return FALSE;
    }
    $this->rrule = $rrule;
    return $this;
  }

  /**
   * Get values from the RRULE formatted for the EventRepeater entity.
   *
   * @return string
   *   Values for an EventRepeater entity.
   */
  public function getEventRepeaterValues() {
    $this->eventRepeaterValues = EventRepeater::DEFAULT_VALUES;
    $components = explode(';', $this->rrule);
    foreach ($components as $component) {
      $componentArray = explode('=', $component);
      switch ($componentArray[0]) {
        case 'FREQ':
          $this->eventRepeaterValues['event_freq'] = $componentArray[1];
          break;

        case 'COUNT':
          $this->eventRepeaterValues['event_count'] = $componentArray[1];
          break;

        case 'UNTIL':
          $this->eventRepeaterValues['event_until'] = $this->getDate($componentArray[1]);
          break;

        case 'INTERVAL':
          $this->eventRepeaterValues['event_interval'] = $componentArray[1];
          break;

        case 'BYMONTH':
          $this->eventRepeaterValues['event_bymonth'] = $componentArray[1];
          break;

        case 'BYMONTHDAY':
          $this->eventRepeaterValues['event_bymonthday'] = $componentArray[1];
          break;

        case 'BYDAY':
          // Assuming FREQ comes before BYDAY.
          if ($this->eventRepeaterValues['event_freq'] !== 'WEEKLY') {
            $this->eventRepeaterValues['event_byday'] = $componentArray[1];
          }
          else {
            $this->eventRepeaterValues['event_byday_multiple'] = $componentArray[1];
          }
          break;

        case 'BYSETPOS':
          $this->eventRepeaterValues['event_bysetpos'] = $componentArray[1];
      }
    }
    return $this->eventRepeaterValues;
  }

  /**
   * Get the RRULE from the EventRepeater entity.
   *
   * @param EventRepeater $eventRepeater
   *   An EventRepeater entity.
   *
   * @return string
   *   The RRULE corresponding to the EventRepeater entity.
   */
  public function setRrule(EventRepeater $eventRepeater) {
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
      case 'WEEKLY':
        $rrule .= ";BYDAY=$byday_multiple";
        break;

      case 'MONTHLY':
        if ($event_repeat_by === 'day_of_the_month') {
          $rrule .= ";BYMONTHDAY=$bymonthday";
        }
        else {
          $rrule .= ";BYSETPOS=$bysetpos;BYDAY=$byday";
        }
        break;

      case 'YEARLY':
        if ($event_repeat_by === 'day_of_the_month') {
          $rrule .= ";BYMONTHDAY=$bymonthday";
        }
        else {
          $rrule .= ";BYSETPOS=$bysetpos;BYDAY=$byday;BYMONTH=$bymonth";
        }
        break;

      case 'none':
      case 'DAILY':
    }
    // Add end configurations.
    switch ($ends) {
      case 'never':
        break;

      case 'COUNT':
        $rrule .= ";COUNT=$count";
        break;

      case 'UNTIL':
        $rrule .= ";UNTIL=$until";
    }
    $this->rrule = $rrule;
    return $this;
  }

  /**
   * Format RRULE date to Drupal date.
   *
   * @param string $date
   *   The RRULE date.
   *
   * @return string
   *   The formatted date.
   */
  private function toDrupalDate($date) {
    $dateObject = DateTime::createFromFormat('Ymd\THis', $date);
    return $dateObject->format('Y-m-d');
  }

  /**
   * Format Drupal date to RRULE date.
   *
   * @param string $date
   *   The Drupal date.
   *
   * @return string
   *   The formatted date.
   */
  private function toRruleDate($date) {
    $dateObject = DateTime::createFromFormat('Ymd\THis', $date);
    return $dateObject->format('Y-m-d');
  }

}
