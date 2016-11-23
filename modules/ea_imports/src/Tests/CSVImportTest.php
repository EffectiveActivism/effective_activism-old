<?php

namespace Drupal\ea_imports\Tests;

use Drupal\ea_permissions\Roles;
use Drupal\ea_groupings\Entity\Grouping;

/**
 * Function tests for ea_imports CSV import entity type.
 *
 * @group effective_activism
 */
class CSVImportTest extends ImportWebTestBase {

  // Test values.
  const STARTDATE = '12/13/2016';
  const STARTTIME = '11:00';
  const ENDDATE = '12/13/2016';
  const ENDTIME = '13:00';

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
   * The test CSV file.
   *
   * @var File
   */
  private $CSVFile;

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
    $this->manager = $this->drupalCreateUser(Roles::MANAGER_PERMISSIONS);
    $this->organizer = $this->drupalCreateUser(Roles::ORGANIZER_PERMISSIONS);
    $this->grouping = $this->createGrouping();
    // Create CSV file.
    $data = file_get_contents($this->container->get('file_system')->realpath(drupal_get_path('module', 'ea_imports') . '/src/Tests/sample.csv'));
    $this->CSVFile = file_save_data($data, 'public://sample.csv', FILE_EXISTS_REPLACE);
  }

  /**
   * Test event entities.
   */
  public function testImports() {
    $this->drupalLogin($this->organizer);
    $this->createCsvImportEntity();
  }

  /**
   * Creates a CSV Import entity.
   */
  private function createCsvImportEntity() {
    $this->drupalGet('effectiveactivism/imports/add/csv');
    $this->assertResponse(200);
    $this->drupalPostForm(NULL, array(
      'grouping[0][target_id]' => $this->grouping->id(),
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'files[field_file_csv_0]' => $this->container->get('file_system')->realpath(drupal_get_path('module', 'ea_imports') . '/src/Tests/sample.csv'),
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the import.', 'Added a new import entity.');
    $this->assertText('One item imported', 'Successfully imported event');
    $this->drupalGet('effectiveactivism/events/1');
    $this->assertResponse(200);
    $this->assertText(sprintf('%s - %s', self::STARTDATE, self::STARTTIME), 'Start date and time found.');
    $this->assertText(sprintf('%s - %s', self::ENDDATE, self::ENDTIME), 'End date and time found.');
  }

  /**
   * Create grouping.
   *
   * @return Grouping
   *   The created grouping.
   */
  private function createGrouping() {
    $grouping = Grouping::create(array(
      'user_id' => $this->manager->id(),
      'name' => ImportWebTestBase::GROUPNAME,
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'managers' => $this->manager->id(),
      'organizers' => $this->organizer->id(),
    ));
    $grouping->save();
    return $grouping;
  }

  /**
   * Create an result type.
   */
  private function createResultType() {
    $this->drupalLogin($this->manager);
    $this->drupalGet('effectiveactivism/result-types');
    $this->assertResponse(200);
    $this->drupalGet('effectiveactivism/result-types/add');
    $this->assertResponse(200);
    // Create an result type.
    $this->drupalPostAjaxForm(NULL, [
      'organization' => $this->grouping->id(),
    ], 'organization');
    $this->drupalPostForm(NULL, array(
      'label' => 'Leafleting',
      'importname' => 'leafleting',
      'description' => 'Sample result',
      'datatypes[leaflets]' => 'leaflets',
      'organization' => $this->grouping->id(),
      'groupings[]' => [$this->grouping->id()],
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the Leafleting Result type.', 'Added a new result type.');
  }

}
