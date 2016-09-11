<?php

/**
 * @file
 * ICalendar test cases for the ea_import module.
 */

namespace Drupal\ea_import\Tests;

use Drupal\ea_import\Tests\ImportWebTestBase;
use Drupal\ea_permissions\Roles;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal;

/**
 * Function tests for ea_import ICalendar import entity type.
 *
 * @group effective_activism
 */
class ICalendarImportTest extends ImportWebTestBase {

  private $grouping;

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
      'name' => ImportWebTestBase::GROUPNAME,
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
    $this->createICalendarImportEntity();
  }

  /**
   * Creates an ICalendar Import entity.
   */
  private function createICalendarImportEntity() {
    $this->drupalGet('effectiveactivism/imports/add/icalendar');
    $this->assertResponse(200);
    $this->drupalPostForm(NULL, array(
      'grouping[0][target_id]' => sprintf('%s (%d)', ImportWebTestBase::GROUPNAME, $this->grouping->id()),
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'field_url[0][uri]' => ImportWebTestBase::GITHUBRAWPATH . '/modules/ea_import/src/Tests/sample.ics',
      'field_continuous_import' => 1,
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the import.', 'Added a new import entity.');
    $this->assertText('One event imported', 'Successfully imported event');
  }
}
