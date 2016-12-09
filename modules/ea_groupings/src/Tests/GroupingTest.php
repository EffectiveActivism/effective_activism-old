<?php

namespace Drupal\ea_groupings\Tests;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_permissions\Roles;
use Drupal\ea_results\Entity\ResultType;
use Drupal\field\Entity\FieldConfig;
use Drupal\simpletest\WebTestBase;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\User;

/**
 * Function tests for ea_groupings.
 *
 * @group effective_activism
 */
class GroupingTest extends WebTestBase {

  public static $modules = array('effective_activism');

  /**
   * Container for the group1 grouping.
   *
   * @var Grouping
   */
  private $group;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $manager = $this->drupalCreateUser(Roles::MANAGER_PERMISSIONS);
    $organizer = $this->drupalCreateUser(Roles::ORGANIZER_PERMISSIONS);
    // Create group and add manager and organizer.
    $grouping = Grouping::create([
      'user_id' => $manager->id(),
      'name' => 'Test group 1',
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'managers' => $manager->id(),
      'organizers' => $organizer->id(),
    ]);
    $grouping->save();
    $this->group = $grouping;
  }

  /**
   * Test grouping entities.
   */
  public function testCreateGrouping() {
    // Verify that the grouping has been created.
    $this->assertNotNull($this->group, 'Grouping created');
    // Verify that vocabulary has been added.
    $vid = sprintf('tags_%d', $this->group->id());
    $vocabulary = Vocabulary::load($vid);
    $this->assertNotNull($vocabulary, 'Vocabulary created');
    // Verify that default result types have been added and contain the tagging field.
    foreach (ResultType::DEFAULT_RESULT_TYPES as $import_name => $settings) {
      $result_type = ResultType::getResultTypeByImportName($import_name, $this->group->id());
      $this->assertNotNull($result_type, sprintf('Result type %s created', $bundle));
      $tagging_field = FieldConfig::loadByName('result', $bundle, $vid);
      $this->assertNotNull($tagging_field, sprintf('Field %s for result type %s exists', $vid, $bundle));
    }
  }

}
