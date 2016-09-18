<?php

namespace Drupal\ea_import;

use Drupal\ea_permissions\Permission;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;

/**
 * Access controller for the Import entity.
 *
 * @see \Drupal\ea_import\Entity\Import.
 */
class ImportAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ea_import\Entity\ImportInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished() &&
          (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity)->isAllowed() ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)->isAllowed())) {
          return new AccessResultAllowed();
        }
        if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity)->isAllowed() ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)->isAllowed()) {
          return new AccessResultAllowed();
        }
        break;

      case 'update':
        if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity)->isAllowed() ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)->isAllowed()) {
          return new AccessResultAllowed();
        }
        break;

      case 'delete':
        if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity)->isAllowed() ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)->isAllowed()) {
          return new AccessResultAllowed();
        }
    }
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add import entities');
  }

}
