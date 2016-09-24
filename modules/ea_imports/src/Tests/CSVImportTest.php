<?php

namespace Drupal\ea_imports\Tests;

use Drupal\ea_permissions\Roles;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_data\Entity\DataType;
use Drupal\ea_activities\Entity\ActivityType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Function tests for ea_imports CSV import entity type.
 *
 * @group effective_activism
 */
class CSVImportTest extends ImportWebTestBase {

  private $grouping;

  private $dataType;

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
    $this->dataType = $this->createLeafletDataType();
    $this->createActivityType();
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
      'grouping[0][target_id]' => sprintf('%s (%d)', ImportWebTestBase::GROUPNAME, $this->grouping->id()),
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
   * Create leaflet data type.
   * 
   * @return DataType
   *   The created leaflet data type.
   */
  private function createLeafletDataType() {
    $bundle = 'leaflets';
    $label = 'Leaflets';
    $entity_type_id = 'data';
    $field_name = 'field_leaflets';
    // Create data type.
    $dataType = DataType::create(array(
      'id' => $bundle,
      'label' => $label,
    ));
    $dataType->save();
    // Add an integer field to the data type.
    $field_storage = FieldStorageConfig::loadByName($entity_type_id, $field_name);
    if (empty($field_storage)) {
      $field_storage = FieldStorageConfig::create(array(
        'field_name' => $field_name,
        'entity_type' => $entity_type_id,
        'type' => 'integer',
        'cardinality' => 1,
        'module' => 'core',
        'settings' => ['min' => '0'],
      ))->save();
    }
    $field = FieldConfig::loadByName($entity_type_id, $bundle, $field_name);
    if (empty($field)) {
      $field = FieldConfig::create(array(
        'field_name' => $field_name,
        'entity_type' => $entity_type_id,
        'bundle' => $bundle,
        'label' => $label,
      ));
      $field
        ->setRequired(TRUE)
        ->save();
    }
    // Form display settings for field_integer_input.
    entity_get_form_display($entity_type_id, $bundle, 'default')
      ->setComponent($field_name, array(
        'type' => 'number',
      ))
      ->save();
    // View display settings for field_integer_input.
    entity_get_display($entity_type_id, $bundle, 'default')
      ->setComponent($field_name, array(
        'type' => 'number_integer',
      ))
      ->save();
    return $dataType;
  }

  /**
   * Create an activity type.
   */
  private function createActivityType() {
    $this->drupalLogin($this->manager);
    $this->drupalGet('effectiveactivism/activity-types');
    $this->assertResponse(200);
    $this->drupalGet('effectiveactivism/activity-types/add');
    $this->assertResponse(200);
    // Create an activity type.
    $this->drupalPostAjaxForm(NULL, [
      'organization' => $this->grouping->id(),
    ], 'organization');
    $this->drupalPostForm(NULL, array(
      'label' => 'Leafleting',
      'id' => 'leafleting',
      'description' => 'Sample activity',
      'data_types' => sprintf('%s (%s)', 'Leaflets', 'leaflets'),
      'organization' => $this->grouping->id(),
      'groupings[]' => [$this->grouping->id()],
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the Leafleting Activity type.', 'Added a new activity type.');
  }
}
