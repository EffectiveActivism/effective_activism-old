<?php

namespace Drupal\ea_permissions;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides functions to manage permissions.
 */
class Permission {

  /**
   * Determines access based on if the user is organizer of the grouping.
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
    if (self::checkPermission($account, $grouping, Roles::ORGANIZER_ROLE)) {
      return new AccessResultAllowed();
    }
    else {
      return new AccessResultForbidden();
    }
  }

  /**
   * Determines access based on if the user is manager of the grouping.
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
    if (self::checkPermission($account, $grouping, Roles::MANAGER_ROLE)) {
      return new AccessResultAllowed();
    }
    else {
      return new AccessResultForbidden();
    }
  }

  /**
   * Determines access based on if the user is in any of the groupings.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which to check a permission.
   * @param array $groupings
   *   The groupings to check relationship for.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns an access result.
   */
  public static function allowedIfInGroupings(AccountInterface $account, $groupings) {
    if (!empty($groupings)) {
      foreach ($groupings as $grouping) {
        if (
          self::checkPermission($account, $grouping, Roles::ORGANIZER_ROLE) ||
          self::checkPermission($account, $grouping, Roles::MANAGER_ROLE)
        ) {
          return new AccessResultAllowed();
        }
      }
    }
    return new AccessResultForbidden();
  }

  /**
   * Determines access based on if the user is in any groupings.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which to check a permission.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns an access result.
   */
  public static function allowedIfInAnyGroupings(AccountInterface $account) {
    if ((int) $account->id() === 1) {
      return new AccessResultAllowed();
    }
    if (!empty(Grouping::getAllGroupingsByUser($account))) {
      return new AccessResultAllowed();
    }
    else {
      return new AccessResultForbidden();
    }
  }

  /**
   * Determines access based on if the user is manager in any groupings.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which to check a permission.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns an access result.
   */
  public static function allowedIfIsManagerInAnyGroupings(AccountInterface $account) {
    if ((int) $account->id() === 1) {
      return new AccessResultAllowed();
    }
    if (!empty(Grouping::getAllGroupingsByRole(Roles::MANAGER_ROLE, $account))) {
      return new AccessResultAllowed();
    }
    else {
      return new AccessResultForbidden();
    }
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
   * @return bool
   *   Whether or not user has access.
   */
  private static function checkPermission(AccountInterface $account, Grouping $grouping, $role) {
    if ((int) $account->id() === 1) {
      return TRUE;
    }
    // Determine access based on role.
    switch ($role) {
      case Roles::ORGANIZER_ROLE:
        // Check if user is organizer of grouping.
        if (in_array(['target_id' => $account->id()], $grouping->get('organizers')->getValue())) {
          return TRUE;
        }
        break;

      case Roles::MANAGER_ROLE:
        // Check if user is manager of grouping.
        if (in_array(['target_id' => $account->id()], $grouping->get('managers')->getValue())) {
          return TRUE;
        }
        // Check if user is manager of parent grouping, if any.
        if (isset($grouping->get('parent')->entity)) {
          $parent = $grouping->get('parent')->entity;
          if (in_array(['target_id' => $account->id()], $parent->get('managers')->getValue())) {
            return TRUE;
          }
        }
    }
    return FALSE;
  }

}
