<?php

namespace Drupal\ea_groupings\Tests;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_permissions\Roles;
use Drupal\simpletest\WebTestBase;
use Drupal\ea_events\Entity\EventRepeater;

/**
 * Function tests for ea_groupings.
 *
 * @group effective_activism
 */
class AccessRestrictionsTest extends WebTestBase {

  public static $modules = array('effective_activism');

  // Test values.
  const GROUPNAME1 = 'Test group 1';
  const GROUPNAME1MODIFIED = 'Test group 1 (updated)';
  const GROUPNAME2 = 'Test group 2';
  const GROUPNAME2MODIFIED = 'Test group 2 (updated)';
  const STARTDATE = '2016-01-01';
  const STARTTIME = '11:00';
  const ENDDATE = '2016-01-01';
  const ENDTIME = '12:00';

  /**
   * Contains the event repeater for the event.
   *
   * @var EventRepeater
   */
  private $eventRepeater;

  /**
   * Container for the group1 grouping.
   *
   * @var Grouping
   */
  private $group1;

  /**
   * Container for the group2 grouping.
   *
   * @var Grouping
   */
  private $group2;

  /**
   * Container for the organizer1 user.
   *
   * @var Drupal\user\Entity\User
   */
  private $organizer1;

  /**
   * Container for the organizer2 user.
   *
   * @var Drupal\user\Entity\User
   */
  private $organizer2;

  /**
   * Container for the manager1 user.
   *
   * @var Drupal\user\Entity\User
   */
  private $manager1;

  /**
   * Container for the manager2 user.
   *
   * @var Drupal\user\Entity\User
   */
  private $manager2;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->manager1 = $this->drupalCreateUser(Roles::MANAGER_PERMISSIONS);
    $this->manager2 = $this->drupalCreateUser(Roles::MANAGER_PERMISSIONS);
    $this->organizer1 = $this->drupalCreateUser(Roles::ORGANIZER_PERMISSIONS);
    $this->organizer2 = $this->drupalCreateUser(Roles::ORGANIZER_PERMISSIONS);
    // Create groups and add managers and organizers.
    $this->group1 = $this->createGrouping(self::GROUPNAME1, $this->manager1, $this->organizer1);
    $this->group2 = $this->createGrouping(self::GROUPNAME2, $this->manager2, $this->organizer2);
  }

  /**
   * Test event entities.
   */
  public function testAccessRestrictions() {
    // Verify that manager1 can manage group 1 and not group 2.
    $this->drupalLogin($this->manager1);
    $this->performGroupingManagement($this->group1, $this->manager1);
    $this->failGroupingManagement($this->group2);
    // Verify that manager1 can create events for group 1.
    $this->drupalLogin($this->manager1);
    $this->performEventManagement($this->group1, $this->manager1);
    // Verify that organizer1 can create events for group 1.
    $this->drupalLogin($this->organizer1);
    $this->performEventManagement($this->group1, $this->organizer1);
    // Verify that organizer2 cannot manage events from group 1.
    $this->drupalLogin($this->organizer2);
    $this->failEventManagement(self::GROUPNAME1MODIFIED);
    // Add manager1 to group 2.
    $this->addManagertoGrouping($this->group2, $this->manager1);
    // Verify that manager1 can manage group 2.
    $this->drupalLogin($this->manager1);
    $this->performGroupingManagement($this->group2, $this->manager1);
    // Verify that manager1 can create events for group 2.
    $this->performEventManagement($this->group2, $this->manager1);
  }

  /**
   * Verify management access to group.
   *
   * @param Grouping $grouping
   *   The grouping to test with.
   * @param Drupal\user\Entity\User $user
   *   The user to test with.
   */
  private function performGroupingManagement($grouping, $user) {
    // User has access to grouping overview page.
    $this->drupalGet('effectiveactivism/groupings');
    $this->assertResponse(200);
    // User has access to grouping page.
    $this->drupalGet(sprintf('effectiveactivism/groupings/%d', $grouping->id()));
    $this->assertResponse(200);
    // User has access to grouping edit page.
    $this->drupalGet(sprintf('effectiveactivism/groupings/%d/edit', $grouping->id()));
    $this->assertResponse(200);
    // User may make changes to grouping.
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $user->getAccountName(), $user->id()),
      'name[0][value]' => self::GROUPNAME1MODIFIED,
      'phone_number[0][value]' => '',
      'email_address[0][value]' => '',
      'location[0][address]' => '',
      'location[0][extra_information]' => '',
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'description[0][value]' => '',
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText(sprintf('Saved the %s Grouping.', self::GROUPNAME1MODIFIED), 'Added a new event entity.');
  }

  /**
   * Verify lack of management access to group.
   *
   * @param Grouping $grouping
   *   The grouping to test with.
   */
  private function failGroupingManagement($grouping) {
    // User doesn't have access to grouping page.
    $this->drupalGet(sprintf('effectiveactivism/groupings/%d', $grouping->id()));
    $this->assertResponse(403);
    // User doesn't have access to grouping edit page.
    $this->drupalGet(sprintf('effectiveactivism/groupings/%d/edit', $grouping->id()));
    $this->assertResponse(403);
  }

  /**
   * Verify organizer access to events.
   *
   * @param Grouping $grouping
   *   The grouping to test with.
   * @param Drupal\user\Entity\User $user
   *   The user to test with.
   */
  private function performEventManagement($grouping, $user) {
    // User has access to event overview page.
    $this->drupalGet('effectiveactivism/events');
    $this->assertResponse(200);
    // User has access to event add page.
    $this->drupalGet('effectiveactivism/events/add');
    $this->assertResponse(200);
    // User may create event.
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $user->getAccountName(), $user->id()),
      'description[0][value]' => '',
      'start_date[0][value][date]' => self::STARTDATE,
      'start_date[0][value][time]' => self::STARTTIME,
      'end_date[0][value][date]' => self::ENDDATE,
      'end_date[0][value][time]' => self::ENDTIME,
      'grouping[0][target_id]' => $grouping->id(),
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created event.', 'Added a new event entity.');
  }

  /**
   * Verify lack of organizer access to events.
   *
   * @param string $groupingName
   *   The grouping to test with.
   */
  private function failEventManagement($groupingName) {
    // User cannot create events with grouping.
    $this->drupalGet('effectiveactivism/events/add');
    $this->assertResponse(200);
    $this->assertNoText($groupingName, 'User does not have access to grouping on event creation pages.');
    // User cannot see events belonging to other groupings.
    $this->drupalGet('effectiveactivism/events');
    $this->assertResponse(200);
    $this->assertText('There is no Event yet.', 'No events visible.');
    // User has no access to event page.
    $this->drupalGet('effectiveactivism/events/1');
    $this->assertResponse(403);
    // User has no access to event edit page.
    $this->drupalGet('effectiveactivism/events/1/edit');
    $this->assertResponse(403);
  }

  /**
   * Add manager to grouping.
   *
   * @param Grouping $grouping
   *   The grouping to add manager to.
   * @param Drupal\user\Entity\User $manager
   *   The manager to add.
   */
  private function addManagertoGrouping($grouping, $manager) {
    $grouping->managers->appendItem($manager->id());
    $grouping->save();
    $this->assertEqual($grouping->get('managers')->getValue()[1]['target_id'], $manager->id());
  }

  /**
   * Create grouping.
   *
   * @param string $groupName
   *   Name of the grouping.
   * @param Drupal\user\Entity\User $manager
   *   The manager of the grouping.
   * @param Drupal\user\Entity\User $organizer
   *   The organizer of the grouping.
   *
   * @return Grouping
   *   The created grouping.
   */
  private function createGrouping($groupName, $manager, $organizer) {
    $grouping = Grouping::create(array(
      'user_id' => $manager->id(),
      'name' => $groupName,
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'managers' => $manager->id(),
      'organizers' => $organizer->id(),
    ));
    $grouping->save();
    return $grouping;
  }

}
