<?php

namespace Drupal\ea_imports\Tests;

use Drupal\ea_groupings\Entity\Grouping;

/**
 * Function tests for ea_imports ICalendar import entity type.
 *
 * @group effective_activism
 */
class ICalendarImportTest extends ImportWebTestBase {

  // Test values.
  const STARTDATE = '01/01/2016';
  const STARTTIME = '11:00';
  const ENDDATE = '01/01/2016';
  const ENDTIME = '12:00';

  /**
   * The test grouping.
   *
   * @var Grouping
   */
  private $grouping;

  /**
   * The test organizer.
   *
   * @var User
   */
  private $organizer;

  /**
   * The test manager.
   *
   * @var User
   */
  private $manager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Disable user time zones.
    // This is required in order for events to register correct time.
    $systemDate = \Drupal::configFactory()->getEditable('system.date');
    $systemDate->set('timezone.default', 'UTC');
    $systemDate->save(TRUE);
    // Create users.
    $this->manager = $this->drupalCreateUser();
    $this->organizer = $this->drupalCreateUser();
    // Create grouping.
    $this->grouping = Grouping::create(array(
      'user_id' => $this->manager->id(),
      'title' => ImportWebTestBase::GROUPTITLE,
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'managers' => $this->manager->id(),
      'organizers' => $this->organizer->id(),
    ));
    $this->grouping->save();
  }

  /**
   * Test event entities.
   */
  public function testImports() {
    $this->drupalLogin($this->organizer);
    $this->createIcalendarImportEntity();
  }

  /**
   * Creates an ICalendar Import entity.
   */
  private function createIcalendarImportEntity() {
    $this->drupalGet('effectiveactivism/imports/add/icalendar');
    $this->assertResponse(200);
    $this->drupalPostForm(NULL, array(
      'grouping[0][target_id]' => $this->grouping->id(),
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'field_url[0][uri]' => ImportWebTestBase::GITHUBRAWPATH . '/modules/ea_imports/src/Tests/sample.ics',
      'field_continuous_import' => 1,
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the import.', 'Added a new import entity.');
    $this->assertText('One item imported', 'Successfully imported event');
    $this->drupalGet('effectiveactivism/events/1');
    $this->assertResponse(200);
    $this->assertText(sprintf('%s - %s', self::STARTDATE, self::STARTTIME), 'Start date and time found.');
    $this->assertText(sprintf('%s - %s', self::ENDDATE, self::ENDTIME), 'End date and time found.');
  }

}
