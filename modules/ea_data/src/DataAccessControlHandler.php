<?php

/**
 * @file
 * Contains \Drupal\ea_data\DataAccessControlHandler.
 */

namespace Drupal\ea_data;

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
        return AccessResult::allowedIfHasPermission($account, 'view data entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit data entities');
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete data entities');
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add data entities');
  }
}
