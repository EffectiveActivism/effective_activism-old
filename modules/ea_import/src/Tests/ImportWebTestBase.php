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
 */
class ImportWebTestBase extends WebTestBase {

  public static $modules = array('effective_activism');

  const GROUPNAME = 'Test group';

  const GITHUBRAWPATH = 'https://raw.githubusercontent.com/EffectiveActivism/effective_activism/development';

}
