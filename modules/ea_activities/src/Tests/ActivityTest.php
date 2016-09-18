<?php

namespace Drupal\ea_activities\Tests;

use Drupal\ea_permissions\Roles;
use Drupal\ea_data\Entity\DataType;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\simpletest\WebTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Function tests for ea_activities.
 *
 * @group effective_activism
 */
class ActivityTest extends WebTestBase {

  const GROUPNAME = 'Test group';

  public static $modules = array('effective_activism');

  private $manager;

  private $organizer;

  private $dataType;

  private $organization;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->manager = $this->drupalCreateUser(Roles::MANAGER_PERMISSIONS);
    $this->organizer = $this->drupalCreateUser(Roles::ORGANIZER_PERMISSIONS);
    // Create data type.
    $this->dataType = DataType::create(array(
      'id' => 'data_type_test',
      'label' => 'Test',
    ));
    $this->dataType->save();
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
    // Create organization grouping.
    $this->organization = Grouping::create(array(
      'user_id' => $this->manager->id(),
      'name' => self::GROUPNAME,
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'managers' => $this->manager->id(),
      'organizers' => $this->organizer->id(),
    ));
    $this->organization->save();
  }

  /**
   * Create an activity entity.
   */
  public function testActivityEntity() {
    $this->createActivityType();
    $this->createActivityEntity();
  }

  /**
   * Create an activity type.
   *
   * Creates a data type called data_type_test and adds a numeric
   * field field_integer_input to it.
   */
  private function createActivityType() {
    $this->drupalLogin($this->manager);
    $this->drupalGet('effectiveactivism/activity-types');
    $this->assertResponse(200);
    $this->drupalGet('effectiveactivism/activity-types/add');
    $this->assertResponse(200);
    // Create an activity type.
    $this->drupalPostAjaxForm(NULL, [
      'organization' => $this->organization->id(),
    ], 'organization');
    $this->drupalPostForm(NULL, array(
      'label' => 'Test',
      'id' => 'activity_type_test',
      'description' => 'Test activity type description',
      'data_types' => sprintf('%s (%s)', 'Test', 'data_type_test'),
      'organization' => $this->organization->id(),
      'groupings[]' => [$this->organization->id()],
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the Test Activity type.', 'Added a new activity type.');
  }

  /**
   * Create an activity content entity.
   *
   * Creates a content entity of type activity_type_test and adds a random
   * numeric value to the field_integer_input field.
   */
  private function createActivityEntity() {
    $this->drupalLogin($this->organizer);
    // Create an activity entity using the activity type.
    $this->drupalGet('effectiveactivism/activities/add/activity_type_test');
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
