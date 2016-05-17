<?php

namespace Drupal\ea_groupings;

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
    /** @var \Drupal\ea_groupings\GroupingInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished grouping entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published grouping entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit grouping entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete grouping entities');
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add grouping entities');
  }
}
