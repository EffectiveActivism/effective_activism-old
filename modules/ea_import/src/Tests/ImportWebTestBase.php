<?php

/**
 * @file
 * Base test class for the ea_import module.
 */

namespace Drupal\ea_import\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal;

/**
 * Function tests for ea_import CSV import entity type.
 *
 * @group effective_activism
 */
class ImportWebTestBase extends WebTestBase {

  public static $modules = array('ea_permissions', 'ea_data', 'ea_activities', 'ea_locations', 'ea_tasks', 'ea_people', 'ea_groupings', 'ea_events', 'ea_import');

  const GROUPNAME = 'Test group';

  const GITHUBRAWPATH = 'https://raw.githubusercontent.com/EffectiveActivism/effective_activism/development';

}
