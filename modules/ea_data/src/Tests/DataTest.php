<?php

namespace Drupal\ea_data\Tests;

use Drupal\ea_data\Entity\Data;
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

  private $dataType;

  private $data;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Create a data entity.
   */
  public function testDataEntity() {
    $this->createDataTypeEntity();
    $this->createDataEntity();
  }

  /**
   * Create a data type entity.
   */
  private function createDataTypeEntity() {
    // Create data type.
    $this->dataType = DataType::create([
      'id' => 'data_type_test',
      'label' => 'Test',
    ]);
    $this->dataType->save();
    // Add an integer field to the data type.
    $field_name = 'field_integer_input';
    $entity_type_id = 'data';
    $bundle = 'data_type_test';
    $label = 'Integer input';
    // Check if field exists and create as necessary.
    $field_storage = FieldStorageConfig::loadByName($entity_type_id, $field_name);
    if (empty($field_storage)) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type_id,
        'type' => 'integer',
        'cardinality' => 1,
        'module' => 'core',
        'settings' => ['min' => '0'],
      ])->save();
    }
    $field = FieldConfig::loadByName($entity_type_id, $bundle, $field_name);
    if (empty($field)) {
      $field = FieldConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type_id,
        'bundle' => $bundle,
        'label' => $label,
      ]);
      $field
        ->setRequired(TRUE)
        ->save();
    }
    // Form display settings for field_integer_input.
    entity_get_form_display($entity_type_id, $bundle, 'default')
      ->setComponent('field_integer_input', [
        'type' => 'number',
      ])
      ->save();
    // View display settings for field_integer_input.
    entity_get_display($entity_type_id, $bundle, 'default')
      ->setComponent('field_integer_input', [
        'type' => 'number_integer',
      ])
      ->save();
  }

  /**
   * Create a data content entity.
   *
   * Creates a content entity of type data_type_test and adds a random
   * numeric value to the field_integer_input field.
   */
  private function createDataEntity() {
    $random_value = rand();
    $this->data = Data::create([
      'type' => 'data_type_test',
      'user_id' => 1,
      'field_integer_input' => $random_value,
    ]);
    $value = $this->data->get('field_integer_input')->getValue();
    $this->assertEqual($value[0]['value'], $random_value);
  }

}
