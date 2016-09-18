<?php

namespace Drupal\ea_import\Tests;

use Drupal\ea_permissions\Roles;
use Drupal\ea_groupings\Entity\Grouping;

/**
 * Function tests for ea_import CSV import entity type.
 *
 * @group effective_activism
 */
class CSVImportTest extends ImportWebTestBase {

  private $grouping;

  private $organizer;

  private $manager;

  private $CSVFile;

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
    // Create CSV file.
    $data = file_get_contents($this->container->get('file_system')->realpath(drupal_get_path('module', 'ea_import') . '/src/Tests/sample.csv'));
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
      'grouping[0][target_id]' => sprintf('%s (%d)', ImportWebTestBase::GROUPNAME, $this->grouping->id()),
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'files[field_file_csv_0]' => $this->container->get('file_system')->realpath(drupal_get_path('module', 'ea_import') . '/src/Tests/sample.csv'),
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the import.', 'Added a new import entity.');
    $this->assertText('One event imported', 'Successfully imported event');
  }

}
