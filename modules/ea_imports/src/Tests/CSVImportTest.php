<?php

namespace Drupal\ea_imports\Tests;

use Drupal\ea_permissions\Roles;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Function tests for ea_imports CSV import entity type.
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
    $this->grouping = $this->createGrouping();
    $this->createResultType();
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
      'id' => 'leafleting',
      'description' => 'Sample result',
      'data_types' => sprintf('%s (%s)', 'Leaflets', 'leaflets'),
      'organization' => $this->grouping->id(),
      'groupings[]' => [$this->grouping->id()],
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the Leafleting Result type.', 'Added a new result type.');
  }

}
