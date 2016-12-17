<?php

namespace Drupal\ea_imports\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Function tests for ea_imports CSV import entity type.
 */
class ImportWebTestBase extends WebTestBase {

  public static $modules = array('effective_activism');

  const GROUPTITLE = 'Test group';

  const GITHUBRAWPATH = 'https://raw.githubusercontent.com/EffectiveActivism/effective_activism/development';

}
