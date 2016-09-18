<?php

namespace Drupal\ea_events\Tests;

use Drupal\ea_permissions\Roles;
use Drupal\simpletest\WebTestBase;
use Drupal\ea_events\Entity\EventRepeater;

/**
 * Test values.
 */
define(__NAMESPACE__ . '\GROUPNAME', 'Test group');
define(__NAMESPACE__ . '\DESCRIPTION', 'Example text for an event description');
define(__NAMESPACE__ . '\STARTDATE', '2016-01-01');
define(__NAMESPACE__ . '\STARTDATEFORMATTED', '01/01/2016');
define(__NAMESPACE__ . '\STARTTIME', '11:00');
define(__NAMESPACE__ . '\ENDDATE', '2016-01-01');
define(__NAMESPACE__ . '\ENDDATEFORMATTED', '01/01/2016');
define(__NAMESPACE__ . '\ENDTIME', '12:00');

/**
 * Function tests for ea_events.
 *
 * @group effective_activism
 */
class EventTest extends WebTestBase {

  public static $modules = array('effective_activism');

  private $eventRepeater;

  private $organizer;

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
    $this->drupalLogin($this->manager);
    $this->createGroupingEntity();
    $this->drupalLogin($this->organizer);
    $this->createEventEntity();
  }

  /**
   * Creates a grouping entity.
   */
  private function createGroupingEntity() {
    // Create a grouping entity.
    $this->drupalGet('effectiveactivism/groupings/add');
    $this->assertResponse(200);
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add existing user" and @data-drupal-selector="edit-organizers-actions-ief-add-existing"]'));
    $this->drupalPostForm(NULL, array(
      'user_id[0][target_id]' => sprintf('%s (%d)', $this->manager->getAccountName(), $this->manager->id()),
      'name[0][value]' => GROUPNAME,
      'timezone' => \Drupal::config('system.date')->get('timezone.default'),
      'organizers[form][entity_id]' => sprintf('%s (%d)', $this->organizer->getAccountName(), $this->organizer->id()),
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText(sprintf('Created the %s Grouping.', GROUPNAME), 'Added a new grouping entity.');
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
      'description[0][value]' => DESCRIPTION,
      'start_date[0][value][date]' => STARTDATE,
      'start_date[0][value][time]' => STARTTIME,
      'end_date[0][value][date]' => ENDDATE,
      'end_date[0][value][time]' => ENDTIME,
      'grouping[0][target_id]' => sprintf('%s (%d)', GROUPNAME, 1),
    ), t('Save'));
    $this->assertResponse(200);
    $this->assertText('Created event.', 'Added a new event entity.');
    $this->assertText(DESCRIPTION, 'Confirmed description was saved.');
    $this->assertText(STARTDATEFORMATTED, 'Confirmed start date was saved.');
    $this->assertText(STARTTIME, 'Confirmed start time was saved.');
    $this->assertText(ENDDATEFORMATTED, 'Confirmed end date was saved.');
    $this->assertText(ENDTIME, 'Confirmed end time was saved.');
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
