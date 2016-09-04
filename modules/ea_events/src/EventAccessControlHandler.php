<?php

/**
 * @file
 * Contains \Drupal\ea_events\EventAccessControlHandler.
 */

namespace Drupal\ea_events;

use Drupal\ea_permissions\Permission;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;

/**
 * Access controller for the Event entity.
 *
 * @see \Drupal\ea_events\Entity\Event.
 */
class EventAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ea_events\EventInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity) ||
            Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)) {
            return new AccessResultAllowed();
          }
        }
        else {
          return AccessResult::allowedIfHasPermission($account, 'view published event entities');
        }
        break;
      case 'update':
        if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity) ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)) {
          return new AccessResultAllowed();
        }
        break;
      case 'delete':
        if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity) ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)) {
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
    return AccessResult::allowedIfHasPermission($account, 'add event entities');
  }

}
