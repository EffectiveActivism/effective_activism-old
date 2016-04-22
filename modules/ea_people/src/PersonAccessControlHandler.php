<?php

/**
 * @file
 * Contains \Drupal\ea_people\PersonAccessControlHandler.
 */

namespace Drupal\ea_people;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Person entity.
 *
 * @see \Drupal\ea_people\Entity\Person.
 */
class PersonAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ea_people\PersonInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished person entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published person entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit person entities');
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete person entities');
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add person entities');
  }
}
