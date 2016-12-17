<?php

namespace Drupal\ea_results\Tests;

use Drupal\ea_data\Entity\DataType;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\simpletest\WebTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Function tests for ea_results.
 *
 * @group effective_activism
 */
class ResultTest extends WebTestBase {

  const GROUPNAME = 'Test group';
  const RESULTTYPEIMPORTNAME = 'result_type_test';

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
    $this->manager = $this->drupalCreateUser();
    $this->organizer = $this->drupalCreateUser();
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
   * Create an result entity.
   */
  public function testResultEntity() {
    $this->createResultType();
  }

  /**
   * Create an result type.
   *
   * Creates a data type called data_type_test and adds a numeric
   * field field_integer_input to it.
   */
  private function createResultType() {
    $this->drupalLogin($this->manager);
    $this->drupalGet('effectiveactivism/result-types');
    $this->assertResponse(200);
    $this->drupalGet('effectiveactivism/result-types/add');
    $this->assertResponse(200);
    // Create an result type.
    $this->drupalPostAjaxForm(NULL, [
      'organization' => $this->organization->id(),
    ], 'organization');
    $this->drupalPostForm(NULL, array(
      'label' => 'Test',
      'importname' => self::RESULTTYPEIMPORTNAME,
      'description' => 'Test result type description',
      'datatypes[data_type_test]' => 'data_type_test',
      'organization' => $this->organization->id(),
      'groupings[]' => [$this->organization->id()],
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the Test Result type.', 'Added a new result type.');
  }

}
