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
  const ORGANIZER_ROLE_ID = 2;

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
  const MANAGER_ROLE_ID = 1;

  /**
   * Defines the manager permissions.
   */
  const MANAGER_PERMISSIONS = array(
    // Location field permissions.
    'use location autocomplete',
    // Use the administration toolbar.
    'access toolbar',
  );

  /**
   * Returns the role name corresponding to the role id.
   *
   * @param int $roleId
   *   The id of the role.
   *
   * @return bool|string
   *   Returns the role name or FALSE if unknown role id.
   */
  static public function getRole($roleId) {
    $role = FALSE;
    switch ($roleId) {
      case self::MANAGER_ROLE_ID:
        $role = self::MANAGER_ROLE;
        break;

      case self::ORGANIZER_ROLE_ID:
        $role = self::ORGANIZER_ROLE;
        break;
    }
    return $role;
  }

}
