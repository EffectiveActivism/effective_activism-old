<?php
/**
 * @file
 * Contains \Drupal\ea_permissions\Roles.
 * 
 */

namespace Drupal\ea_permissions;

class Roles {

  /**
   * Defines the organizer role.
   */
  const ORGANIZER_ROLE = 'organizer';

  /**
   * Defines the organizer permissions.
   */
  const ORGANIZER_PERMISSIONS = array(
    // People permissions.
    'add person entities',
    'delete person entities',
    'edit person entities',
    'view published person entities',
    // Grouping permissions.
    'view published grouping entities',
    // Activity permissions.
    'add activity entities',
    'delete activity entities',
    'edit activity entities',
    'view published activity entities',
    // Data permissions.
    'add data entities',
    'edit data entities',
    'view data entities',
    // Event permissions.
    'add event entities',
    // Task permissions.
    'add task entities',
    'delete task entities',
    'edit task entities',
    'view published task entities',
    'view unpublished task entities',
    // Import permissions.
    'access import overview',
    'add import entities',
    'edit import entities',
    'view published task entities',
    'view unpublished task entities',
  );

  /**
   * Defines the manager role.
   */
  const MANAGER_ROLE = 'manager';

  /**
   * Defines the manager permissions.
   */
  const MANAGER_PERMISSIONS = array(
    // People permissions.
    'add person entities',
    'delete person entities',
    'edit person entities',
    'view published person entities',
    // Grouping permissions.
    'add grouping entities',
    'edit grouping entities',
    'view published grouping entities',
    'view unpublished grouping entities',
    // Activity permissions.
    'administer activity entities',
    'add activity entities',
    'delete activity entities',
    'edit activity entities',
    'view published activity entities',
    // Data permissions.
    'administer data entities',
    'administer data fields',
    'administer data display',
    // Event permissions.
    'add event entities',
    // Task permissions.
    'add task entities',
    'delete task entities',
    'edit task entities',
    'view published task entities',
    'view unpublished task entities',
    // Import permissions.
    'access import overview',
    'add import entities',
    'edit import entities',
    'view published task entities',
    'view unpublished task entities',
  );
}
