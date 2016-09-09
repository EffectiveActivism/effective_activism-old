<?php
/**
 * @file
 * Contains \Drupal\ea_permissions\Permission.
 * 
 */

namespace Drupal\ea_permissions;

use Drupal\ea_permissions\Roles;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides functions to manage permissions.
 */
class Permission {

  /**
   * Creates an allowed access result if the user is organizer of the grouping, denied otherwise.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which to check a permission.
   * @param Grouping $grouping
   *   The grouping to check relationship for.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns an access result.
   */
  public static function allowedIfIsOrganizer(AccountInterface $account, Grouping $grouping) {
    return self::checkPermission($account, $grouping, Roles::ORGANIZER_ROLE);
  }

  /**
   * Creates an allowed access result if the user is manager of the grouping, denied otherwise.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which to check a permission.
   * @param Grouping $grouping
   *   The grouping to check relationship for.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns an access result.
   */
  public static function allowedIfIsManager(AccountInterface $account, Grouping $grouping) {
    return self::checkPermission($account, $grouping, Roles::MANAGER_ROLE);
  }

  /**
   * Returns access based on grouping and role.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which to check a permission.
   * @param \Drupal\ea_grouping\Entity\Grouping $grouping
   *   The grouping to check relationship for.
   * @param string $role
   *   The role to check for.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns an access result.
   */
  private static function checkPermission(AccountInterface $account, Grouping $grouping, String $role) {
    // Allow access for administrators.
    foreach ($account->getRoles() as $userRole) {
      if ($userRole === 'administrator') {
        return new AccessResultAllowed();
      }
    }
    // Default response is to deny access.
    $access = new AccessResultForbidden();
    // Determine access based on role.
    switch ($role) {
      case Roles::ORGANIZER_ROLE :
        // Check if user is organizer of grouping.
        if (in_array($account->id(), $grouping->get('organizers')->getValue())) {
          $access = new AccessResultAllowed();
          break;
        }
      case Roles::MANAGER_ROLE :
        // Check if user is manager of grouping.
        if (in_array($account->id(), $grouping->get('managers')->getValue())) {
          $access = new AccessResultAllowed();
          break;
        }
        // Check if user is manager of parent grouping, if any.
        if (isset($grouping->get('parent')->entity)) {
          $parent = $grouping->get('parent')->entity;
          if (in_array($account->id(), $parent->get('managers')->referencedEntities())) {
            $access = new AccessResultAllowed();
            break;
          }
        }
    }
    return $access;
  }
}
