<?php

namespace Drupal\ea_data\Tests;

use Drupal\ea_data\Entity\DataType;
use Drupal\ea_permissions\Roles;
use Drupal\simpletest\WebTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Function tests for ea_data.
 *
 * @group effective_activism
 */
class DataTest extends WebTestBase {

  public static $modules = array('effective_activism');

  private $organizer;

  private $dataType;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
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
  }

  /**
   * Create a data entity.
   */
  public function testDataEntity() {
    $this->createDataEntity();
  }

  /**
   * Create a data content entity.
   *
   * Creates a content entity of type data_type_test and adds a random
   * numeric value to the field_integer_input field.
   */
  private function createDataEntity() {
    $this->drupalLogin($this->organizer);
    // Create a data entity using the data type.
    $this->drupalGet('effectiveactivism/data/add/data_type_test');
    $this->assertResponse(200);
    $random_value = rand();
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'field_integer_input[0][value]' => $random_value,
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created data', 'Added a new data entity.');
    $this->assertText($random_value, 'Confirmed value was saved.');
  }

}
