<?php

namespace Drupal\ea_imports\Tests;

use Drupal\ea_permissions\Roles;
use Drupal\ea_groupings\Entity\Grouping;

/**
 * Function tests for ea_imports ICalendar import entity type.
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
    $this->createIcalendarImportEntity();
  }

  /**
   * Creates an ICalendar Import entity.
   */
  private function createIcalendarImportEntity() {
    $this->drupalGet('effectiveactivism/imports/add/icalendar');
    $this->assertResponse(200);
    $this->drupalPostForm(NULL, array(
      'grouping[0][target_id]' => sprintf('%s (%d)', ImportWebTestBase::GROUPNAME, $this->grouping->id()),
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'field_url[0][uri]' => ImportWebTestBase::GITHUBRAWPATH . '/modules/ea_imports/src/Tests/sample.ics',
      'field_continuous_import' => 1,
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the import.', 'Added a new import entity.');
    $this->assertText('One event imported', 'Successfully imported event');
  }

}
