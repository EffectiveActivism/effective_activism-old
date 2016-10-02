<?php

namespace Drupal\ea_groupings;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_permissions\Permission;
use Drupal\ea_permissions\Roles;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Grouping entity.
 *
 * @see \Drupal\ea_groupings\Entity\Grouping.
 */
class GroupingAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return Permission::allowedIfIsManager($account, $entity);
        }
        else {
          return Permission::allowedIfInGroupings($account, [$entity]);
        }
      case 'update':
        return Permission::allowedIfIsManager($account, $entity);

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
    return Permission::allowedIfIsManagerInAnyGroupings($account);
  }

}
