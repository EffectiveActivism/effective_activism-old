<?php

/**
 * @file
 * Test cases for the ea_events module.
 */

namespace Drupal\ea_events\Tests;

use Drupal\ea_events\Entity\Event;
use Drupal\simpletest\WebTestBase;
use Drupal;

define(__NAMESPACE__ . '\GROUPNAME', 'Test group');
define(__NAMESPACE__ . '\DESCRIPTION', 'Example text for an event description');
define(__NAMESPACE__ . '\STARTDATE', '2016-01-01');
define(__NAMESPACE__ . '\STARTDATEFORMATTED', '01/01/2016');
define(__NAMESPACE__ . '\STARTTIME', '11:00');
define(__NAMESPACE__ . '\ENDDATE', '2016-01-01');
define(__NAMESPACE__ . '\ENDDATEFORMATTED', '01/01/2016');
define(__NAMESPACE__ . '\ENDTIME', '12:00');

/**
 * Function tests for ea_events.
 *
 * @group effective_activism
 */
class EventTest extends WebTestBase {

  public static $modules = array('telephone', 'inline_entity_form', 'ea_data', 'ea_activities', 'ea_locations', 'ea_tasks', 'ea_people', 'ea_groupings', 'ea_events');

  private $organizer;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->organizer = $this->drupalCreateUser(array(
      // Event permissions.
      'add event entities',
      'delete event entities',
      'edit event entities',
      'view published event entities',
      // Grouping permissions.
      'add grouping entities',
      'delete grouping entities',
      'edit grouping entities',
      'view published grouping entities',
      // Activity permissions.
      'add activity entities',
      'delete activity entities',
      'edit activity entities',
      'view published activity entities',
      // Task permissions.
      'add task entities',
      'delete task entities',
      'edit task entities',
      'view published task entities',
      // People permissions.
      'add person entities',
      'delete person entities',
      'edit person entities',
      'view published person entities',
      // Data permissions.
      'add data entities',
      'delete data entities',
      'edit data entities',
      'view data entities',
      // Event repeater permissions.
      'add event repeater entities',
      'delete event repeater entities',
      'edit event repeater entities',
      'view event repeater entities',
    ));
  }

  /**
   * Test event entities.
   */
  public function testEvents() {
    $this->drupalLogin($this->organizer);
    $this->createGroupingEntity();
    $this->createEventEntity();
  }

  /**
   * Creates a grouping entity.
   */
  private function createGroupingEntity() {
    // Create a grouping entity.
    $this->drupalGet('effectiveactivism/groupings/add');
    $this->assertResponse(200);
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'name[0][value]' => GROUPNAME,
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText(sprintf('Created the %s Grouping.', GROUPNAME), 'Added a new grouping entity.');
  }

  /**
   * Create an event entity.
   */
  private function createEventEntity() {
    // Create an event entity.
    $this->drupalGet('effectiveactivism/events/add');
    $this->assertResponse(200);
    $random_value = rand();
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'description[0][value]' => DESCRIPTION,
      'start_date[0][value][date]' => STARTDATE,
      'start_date[0][value][time]' => STARTTIME,
      'end_date[0][value][date]' => ENDDATE,
      'end_date[0][value][time]' => ENDTIME,
      'grouping[0][target_id]' => sprintf('%s (%d)', GROUPNAME, 1),
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created event.', 'Added a new event entity.');
    $this->assertText(DESCRIPTION, 'Confirmed description was saved.');
    $this->assertText(STARTDATEFORMATTED, 'Confirmed start date was saved.');
    $this->assertText(STARTTIME, 'Confirmed start time was saved.');
    $this->assertText(ENDDATEFORMATTED, 'Confirmed end date was saved.');
    $this->assertText(ENDTIME, 'Confirmed end time was saved.');
  }
}
