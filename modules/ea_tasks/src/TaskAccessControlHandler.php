<?php

namespace Drupal\ea_tasks;

use Drupal\ea_permissions\Permission;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Task entity.
 *
 * @see \Drupal\ea_tasks\Entity\Task.
 */
class TaskAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return Permission::allowedIfIsManagerInAnyGroupings($account);
        }
        return Permission::allowedIfInAnyGroupings($account);

      case 'update':
        return Permission::allowedIfInAnyGroupings($account);

      case 'delete':
        return AccessResult::forbidden();

    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return Permission::allowedIfInAnyGroupings($account);
  }

}
