<?php

namespace Drupal\ea_imports;

use Drupal\ea_permissions\Permission;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Import entity.
 *
 * @see \Drupal\ea_imports\Entity\Import.
 */
class ImportAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $grouping = $entity->get('grouping')->entity;
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return Permission::allowedIfIsManager($account, $grouping);
        }
        return Permission::allowedIfInGroupings($account, [$grouping]);

      case 'update':
        return Permission::allowedIfInGroupings($account, [$grouping]);

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
