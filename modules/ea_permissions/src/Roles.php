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
    // People permissions.
    'add person entities',
    'edit person entities',
    'view published person entities',
    // Grouping permissions.
    'view published grouping entities',
    // Result permissions.
    'add result entities',
    'edit result entities',
    'view published result entities',
    // Data permissions.
    'add data entities',
    'edit data entities',
    'view data entities',
    'administer data entities',
    // Event permissions.
    'add event entities',
    'view published event entities',
    // Event repeater permissions.
    'view event repeater entities',
    'edit event repeater entities',
    'add event repeater entities',
    // Location field permissions.
    'use location autocomplete',
    // Task permissions.
    'add task entities',
    'edit task entities',
    'view published task entities',
    'view unpublished task entities',
    // Import permissions.
    'add import entities',
    'view import entities',
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
    // People permissions.
    'add person entities',
    'edit person entities',
    'view published person entities',
    // Grouping permissions.
    'add grouping entities',
    'view published grouping entities',
    // Result permissions.
    'administer result entities',
    'add result entities',
    'edit result entities',
    'view published result entities',
    // Data permissions.
    'add data entities',
    'edit data entities',
    'view data entities',
    'administer data entities',
    // Event permissions.
    'add event entities',
    'view published event entities',
    // Event repeater permissions.
    'view event repeater entities',
    'edit event repeater entities',
    'add event repeater entities',
    // Location field permissions.
    'use location autocomplete',
    // Task permissions.
    'add task entities',
    'edit task entities',
    'view published task entities',
    'view unpublished task entities',
    // Import permissions.
    'add import entities',
    'view import entities',
    // Use the administration toolbar.
    'access toolbar',
  );

}
