<?php

/**
 * @file
 * ICalendar test cases for the ea_import module.
 */

namespace Drupal\ea_import\Tests;

use Drupal\ea_import\Entity\Import;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_events\Entity\Event;
use Drupal\ea_permissions\Roles;
use Drupal\simpletest\WebTestBase;
use Drupal\ea_events\Entity\EventRepeater;
use Drupal;

/**
 * Test values.
 */
define(__NAMESPACE__ . '\GROUPNAME', 'Test group');
define(__NAMESPACE__ . '\DESCRIPTION', 'Example text for an event description');
define(__NAMESPACE__ . '\STARTDATE', '2016-01-01');
define(__NAMESPACE__ . '\STARTDATEFORMATTED', '01/01/2016');
define(__NAMESPACE__ . '\STARTTIME', '11:00');
define(__NAMESPACE__ . '\ENDDATE', '2016-01-01');
define(__NAMESPACE__ . '\ENDDATEFORMATTED', '01/01/2016');
define(__NAMESPACE__ . '\ENDTIME', '12:00');
define(__NAMESPACE__ . '\GITHUBRAWPATH', 'https://raw.githubusercontent.com/EffectiveActivism/effective_activism/development');

/**
 * Function tests for ea_data.
 *
 * @group effective_activism
 */
class ICalendarImportTest extends WebTestBase {

  public static $modules = array('ea_permissions', 'ea_data', 'ea_activities', 'ea_locations', 'ea_tasks', 'ea_people', 'ea_groupings', 'ea_events', 'ea_import');

  private $grouping;

  private $event;

  private $eventRepeater;

  private $organizer;

  private $manager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->manager = $this->drupalCreateUser(Roles::MANAGER_PERMISSIONS);
    $this->organizer = $this->drupalCreateUser(Roles::ORGANIZER_PERMISSIONS);
    // Create grouping.
    $this->grouping = Grouping::create(array(
      'user_id' => $this->manager->id(),
      'name' => GROUPNAME,
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'managers' => $this->manager->id(),
      'organizers' => $this->organizer->id(),
    ));
    $this->grouping->save();
    // Create event repeater.
    $this->eventRepeater = EventRepeater::create(EventRepeater::DEFAULT_VALUES);
    // Create event.
    $this->event = Event::create(array(
      'user_id' => $this->organizer->id(),
      'description' => DESCRIPTION,
      'start_date[' => STARTDATE,
      'start_date' => STARTTIME,
      'end_date' => ENDDATE,
      'end_date' => ENDTIME,
      'grouping' => $this->grouping->id(),
    ));
    $this->event->save();
  }

  /**
   * Test event entities.
   */
  public function testImports() {
    $this->drupalLogin($this->organizer);
    $this->createICalendarImportEntity();
  }

  /**
   * Creates a grouping entity.
   */
  private function createICalendarImportEntity() {
    // Create a grouping entity.
    $this->drupalGet('effectiveactivism/imports/add/icalendar');
    $this->assertResponse(200);
    $this->drupalPostForm(NULL, array(
      'grouping[0][target_id]' => sprintf('%s (%d)', GROUPNAME, $this->grouping->id()),
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'field_url[0][uri]' => GITHUBRAWPATH . '/modules/ea_import/src/Tests/sample.ics',
      'field_continuous_import' => 1,
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the import.', 'Added a new import entity.');
  }
}
