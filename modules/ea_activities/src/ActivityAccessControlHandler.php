<?php

/**
 * @file
 * Contains \Drupal\ea_activities\ActivityAccessControlHandler.
 */

namespace Drupal\ea_activities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Activity entity.
 *
 * @see \Drupal\ea_activities\Entity\Activity.
 */
class ActivityAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ea_activities\ActivityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished activity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published activity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit activity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete activity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add activity entities');
  }

}
