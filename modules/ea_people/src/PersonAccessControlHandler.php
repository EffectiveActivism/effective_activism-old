<?php

namespace Drupal\ea_people;

use Drupal\ea_permissions\Permission;
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
          return Permission::allowedIfIsManagerInAnyGroupings($account);
        }
        return Permission::allowedIfInAnyGroupings($account);

      case 'update':
        return Permission::allowedIfIsManagerInAnyGroupings($account);

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
