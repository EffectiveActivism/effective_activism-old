<?php

namespace Drupal\ea_groupings\Component;

use Drupal\user\Entity\User;
use Drupal\ea_permissions\Roles;

use Exception;

/**
 * Provides helper functions for grouping invitations.
 */
class Invitation {

  const DATABASE_TABLE = 'ea_groupings_invitations';
  const INVITATION_STATUS_ALREADY_MANAGER = 0;
  const INVITATION_STATUS_ALREADY_ORGANIZER = 1;
  const INVITATION_STATUS_INVITED = 2;
  const INVITATION_STATUS_NEW_USER = 3;
  const INVITATION_STATUS_EXISTING_USER = 4;

  /**
   * Checks if user is already a manager of the grouping or invited to be one.
   *
   * @param int $gid
   *   The grouping id to check for.
   * @param string $email
   *   The user to check for.
   *
   * @return int
   *   A status code
   */
  static public function getManagerStatus($gid, $email) {
    // Check if user exists.
    $user = user_load_by_mail($email);
    if ($user === FALSE) {
      return self::INVITATION_STATUS_NEW_USER;
    }
    // Check that user isn't already invited.
    $email = $user->getEmail();
    $invitations = db_select(self::DATABASE_TABLE, 'invitation')
      ->fields('invitation')
      ->condition('email', $email)
      ->condition('roleid', Roles::MANAGER_ROLE_ID)
      ->countQuery()
      ->execute()
      ->fetchField();
    if ($invitations > 0) {
      return self::INVITATION_STATUS_INVITED;
    }
    // Check that user is already a manager of the grouping.
    $groupings = \Drupal::entityQuery('grouping')
      ->condition('managers', $user->id())
      ->condition('id', $gid)
      ->execute();
    if (count($groupings) > 0) {
      return self::INVITATION_STATUS_ALREADY_MANAGER;
    }
    else {
      return self::INVITATION_STATUS_EXISTING_USER;
    }
  }

  /**
   * Checks if user is already organizer of the grouping or invited to be one.
   *
   * @param int $gid
   *   The grouping id to check for.
   * @param User $user
   *   The user to check for.
   *
   * @return int
   *   A status code
   */
  static public function getOrganizerStatus($gid, $email) {
    // Check if user exists.
    $user = user_load_by_mail($email);
    if ($user === FALSE) {
      return self::INVITATION_STATUS_NEW_USER;
    }
    // Check that user isn't already invited.
    $email = $user->getEmail();
    $invitations = db_select(self::DATABASE_TABLE, 'invitation')
      ->fields('invitation')
      ->condition('email', $email)
      ->condition('roleid', Roles::ORGANIZER_ROLE_ID)
      ->countQuery()
      ->execute()
      ->fetchField();
    if ($invitations > 0) {
      return self::INVITATION_STATUS_INVITED;
    }
    // Check that user is already a manager of the grouping.
    $groupings = \Drupal::entityQuery('grouping')
      ->condition('organizers', $user->id())
      ->condition('id', $gid)
      ->execute();
    if (count($groupings) > 0) {
      return self::INVITATION_STATUS_ALREADY_ORGANIZER;
    }
    else {
      return self::INVITATION_STATUS_EXISTING_USER;
    }
  }

  /**
   * Add an invitation for a user.
   *
   * @param int $gid
   *   The grouping id.
   * @param int $roleId
   *   The role id.
   * @param string $email
   *   The email address to invite.
   *
   * @return bool|int
   *   The id of the invitation or FALSE if the operation failed.
   */
  static public function addInvition($gid, $roleId, $email) {
    try {
      return db_insert(self::DATABASE_TABLE)
        ->fields([
          'created' => time(),
          'email' => $email,
          'gid' => $gid,
          'roleid' => $roleId,
        ])
        ->execute();
    }
    catch (Exception $e) {
      \Drupal::logger('ea_groupings')->error('Failed to add invitation.');
      return FALSE;
    }
  }

  /**
   * Delete an invitation for a user.
   *
   * @param int $id
   *   The invitation id.
   *
   * @return bool|int
   *   The number of deleted invitations or FALSE if the operation failed.
   */
  static public function removeInvition($id) {
    try {
      return db_delete(self::DATABASE_TABLE)
        ->condition('id', $id)
        ->execute();
    }
    catch (Exception $e) {
      \Drupal::logger('ea_groupings')->error('Failed to delete invitation.');
      return FALSE;
    }
  }

  /**
   * Retrieves a list of invitations identified by e-mail.
   *
   * @param string $email
   *   The email to check invitations for.
   *
   * @return bool|array
   *   A list of invitations or FALSE if the operation failed.
   */
  static public function getInvitations($email) {
    try {
      return db_select(self::DATABASE_TABLE, 'invitation')
        ->fields('invitation')
        ->condition('email', $email)
        ->execute()
        ->fetchAll();
    }
    catch (Exception $e) {
      \Drupal::logger('ea_groupings')->error('Failed to retrieve invitations.');
      return FALSE;
    }
  }

}
