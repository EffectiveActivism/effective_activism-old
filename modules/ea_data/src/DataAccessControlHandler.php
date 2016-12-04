<?php

namespace Drupal\ea_data;

use Drupal\ea_permissions\Permission;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Data entity.
 *
 * @see \Drupal\ea_data\Entity\Data.
 */
class DataAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ea_data\DataInterface $entity */
    switch ($operation) {
      case 'view':
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
