<?php

/**
 * @file
 * Test cases for Content Entity Example Module.
 */

namespace Drupal\ea_data\Tests;

use Drupal\ea_data\Entity\Data;
use Drupal\simpletest\WebTestBase;

/**
 * Function tests for ea_data.
 *
 * @group ea_data
 */
class DataTest extends WebTestBase {

  public static $modules = array('ea_data', 'field_ui');

  private $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser(array(
      'add data entities',
      'administer data entities',
      'delete data entities',
      'edit data entities',
      'view data entities',
      'administer data fields'
    ));
  }

  /**
   * Test the data entity.
   * 
   * Create a data type and add an integer field to it.
   * Then create a data entity using the data type. 
   */
  public function testDataEntity() {
    $this->drupalLogin($this->user);
    $this->drupalGet('effectiveactivism/data_type');
    $this->assertResponse(200);
    $this->drupalGet('effectiveactivism/data_type/add');
    $this->assertResponse(200);
    // Create a data type.
    $this->drupalPostForm(NULL, array(
      'label' => 'Test',
      'id' => 'data_type_test',
      'description' => 'Test data type description',
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created the Test Data type.', 'Added a new data type.');
    $this->drupalGet('effectiveactivism/data_type/data_type_test/edit/fields/add-field');
    // Create a field for the data type.
    $this->drupalPostForm(NULL, array(
      'new_storage_type' => 'integer',
      'label' => 'Integer input',
      'field_name' => 'integer_input',
    ), t('Save and continue'));
    $this->assertResponse(200);
    $this->assertText('Integer input', 'Added an integer field to the data type.');
    // Set cardinality of the field.
    $this->drupalPostForm(NULL, array(
      'cardinality' => 'number',
      'cardinality_number' => '1',
    ), t('Save field settings'));
    $this->assertResponse(200);
    $this->assertText('Updated field Integer input field settings.', 'Set cardinality for integer field.');
    // Settings for the field.
    $this->drupalPostForm(NULL, array(
      'settings[min]' => '0',
    ), t('Save settings'));
    $this->assertResponse(200);
    $this->assertText('Saved Integer input configuration.', 'Saved settings for integer field.');
    // Create a data entity using the data type.
    $this->drupalGet('effectiveactivism/data/add/data_type_test');
    $this->assertResponse(200);
    $random_value = rand();
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->user->getAccountName(), $this->user->id()),
      'field_integer_input[0][value]' => $random_value,
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created data', 'Added a new data entity.');
    $this->assertText($random_value, 'Confirmed value was saved.');
  }
}
