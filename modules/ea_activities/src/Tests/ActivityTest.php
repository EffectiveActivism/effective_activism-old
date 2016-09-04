<?php

/**
 * @file
 * Test cases for the ea_activities module.
 */

namespace Drupal\ea_activities\Tests;

use Drupal\ea_permissions\Roles;
use Drupal\ea_activities\Entity\Activity;
use Drupal\ea_data\Entity\DataType;
use Drupal\ea_data\Entity\Data;
use Drupal\simpletest\WebTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Function tests for ea_activities.
 *
 * @group effective_activism
 */
class ActivityTest extends WebTestBase {

  public static $modules = array('ea_permissions', 'ea_data', 'ea_activities', 'ea_locations', 'ea_tasks', 'ea_people', 'ea_groupings', 'ea_events', 'ea_import');

  private $manager;

  private $organizer;

  private $data_type;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->manager = $this->drupalCreateUser(Roles::MANAGER_PERMISSIONS);
    $this->organizer = $this->drupalCreateUser(Roles::ORGANIZER_PERMISSIONS);
    // Create data type.
    $this->data_type = DataType::create(array(
      'id' => 'data_type_test',
      'label' => 'Test',
    ));
    $this->data_type->save();
    // Add an integer field to the data type.
    $field_name = 'field_integer_input';
    $entity_type_id = 'data';
    $bundle = 'data_type_test';
    $label = 'Integer input';
    // Check if field exists and create as necessary.
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
    ->setComponent('field_integer_input', array(
      'type' => 'number',
    ))
    ->save();
    // View display settings for field_integer_input.
    entity_get_display($entity_type_id, $bundle, 'default')
    ->setComponent('field_integer_input', array(
      'type' => 'number_integer',
    ))
    ->save();
  }

  /**
   * Create an activity entity.
   */
  public function testActivityEntity() {
    $this->createActivityType();
    $this->createActivityEntity();
  }

  /**
   * Create a data type.
   * 
   * Creates a data type called data_type_test and adds a numeric
   * field field_integer_input to it.
   */
  private function createActivityType() {
    $this->drupalLogin($this->manager);
    $this->drupalGet('effectiveactivism/activity_type');
    $this->assertResponse(200);
    $this->drupalGet('effectiveactivism/activity_type/add');
    $this->assertResponse(200);
    // Create a data type.
    $this->drupalPostForm(NULL, array(
      'label' => 'Test',
      'id' => 'activity_type_test',
      'description' => 'Test activity type description',
      'data_types' => sprintf('%s (%s)', 'Test', 'data_type_test'),
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the Test Activity type.', 'Added a new activity type.');
  }

  /**
   * Create a data content entity.
   * 
   * Creates a content entity of type data_type_test and adds a random
   * numeric value to the field_integer_input field.
   */
  private function createActivityEntity() {
    $this->drupalLogin($this->organizer);
    // Create an activity entity using the activity type.
    $this->drupalGet('effectiveactivism/activity/add');
    $this->assertResponse(200);
    $random_value = rand();
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'field_data_type_test[0][inline_entity_form][user_id][0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'field_data_type_test[0][inline_entity_form][field_integer_input][0][value]' => $random_value,
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created an Activity.', 'Added a new activity entity.');
    $this->assertText($random_value, 'Confirmed value was saved.');
  }
}
