<?php

namespace Drupal\ea_permissions;

/**
 * Defines roles other modules to use.
 */
class Roles {

  /**
   * Defines the organizer role.
   */
  const ORGANIZER_ROLE = 'organizer';

  /**
   * Defines the organizer permissions.
   */
  const ORGANIZER_PERMISSIONS = array(
    // Location field permissions.
    'use location autocomplete',
    // Use the administration toolbar.
    'access toolbar',
  );

  /**
   * Defines the manager role.
   */
  const MANAGER_ROLE = 'manager';

  /**
   * Defines the manager permissions.
   */
  const MANAGER_PERMISSIONS = array(
    // Location field permissions.
    'use location autocomplete',
    // Use the administration toolbar.
    'access toolbar',
  );

}
