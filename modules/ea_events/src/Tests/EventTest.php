<?php

namespace Drupal\ea_events\Tests;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_permissions\Roles;
use Drupal\simpletest\WebTestBase;
use Drupal\ea_events\Entity\EventRepeater;

/**
 * Function tests for ea_events.
 *
 * @group effective_activism
 */
class EventTest extends WebTestBase {

  public static $modules = array('effective_activism');

  // Test values.
  const GROUPNAME = 'Test group';

  const DESCRIPTION = 'Example text for an event description';

  const STARTDATE = '2016-01-01';

  const STARTDATEFORMATTED = '01/01/2016';

  const STARTTIME = '11:00';

  const ENDDATE = '2016-01-01';

  const ENDDATEFORMATTED = '01/01/2016';

  const ENDTIME = '12:00';

  /**
   * Contains the event repeater for the event.
   *
   * @var EventRepeater
   */
  private $eventRepeater;

  /**
   * Container for the organizer user.
   *
   * @var Drupal\user\Entity\User
   */
  private $organizer;

  /**
   * Container for the manager user.
   *
   * @var Drupal\user\Entity\User
   */
  private $manager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->manager = $this->drupalCreateUser(Roles::MANAGER_PERMISSIONS);
    $this->organizer = $this->drupalCreateUser(Roles::ORGANIZER_PERMISSIONS);
    // Create event repeater.
    $this->eventRepeater = EventRepeater::create(EventRepeater::DEFAULT_VALUES);
  }

  /**
   * Test event entities.
   */
  public function testEvents() {
    $this->createGrouping();
    $this->drupalLogin($this->organizer);
    $this->createEventEntity();
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
      'name' => self::GROUPNAME,
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'managers' => $this->manager->id(),
      'organizers' => $this->organizer->id(),
    ));
    $grouping->save();
    return $grouping;
  }

  /**
   * Create an event entity.
   */
  private function createEventEntity() {
    // Create an event entity.
    $this->drupalGet('effectiveactivism/events/add');
    $this->assertResponse(200);
    $random_value = rand();
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
      'description[0][value]' => self::DESCRIPTION,
      'start_date[0][value][date]' => self::STARTDATE,
      'start_date[0][value][time]' => self::STARTTIME,
      'end_date[0][value][date]' => self::ENDDATE,
      'end_date[0][value][time]' => self::ENDTIME,
      'grouping[0][target_id]' => 1,
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created event.', 'Added a new event entity.');
    $this->assertText(self::DESCRIPTION, 'Confirmed description was saved.');
    $this->assertText(self::STARTDATEFORMATTED, 'Confirmed start date was saved.');
    $this->assertText(self::STARTTIME, 'Confirmed start time was saved.');
    $this->assertText(self::ENDDATEFORMATTED, 'Confirmed end date was saved.');
    $this->assertText(self::ENDTIME, 'Confirmed end time was saved.');
  }

  /**
   * Gets IEF button name.
   *
   * @param array $xpath
   *   Xpath of the button.
   *
   * @return string
   *   The name of the button.
   */
  protected function getButtonName($xpath) {
    $retval = '';
    /** @var \SimpleXMLElement[] $elements */
    if ($elements = $this->xpath($xpath)) {
      foreach ($elements[0]->attributes() as $name => $value) {
        if ($name == 'name') {
          $retval = $value;
          break;
        }
      }
    }
    return $retval;
  }

}
