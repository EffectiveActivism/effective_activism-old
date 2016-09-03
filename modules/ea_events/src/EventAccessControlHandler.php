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
        if (!$entity->isPublished() &&
          (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity) ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity))) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished event entities');
        }
        if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity) ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)) {
          return AccessResult::allowedIfHasPermission($account, 'view published event entities');
        }
        break;
      case 'update':
        if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity) ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)) {
          return AccessResult::allowedIfHasPermission($account, 'edit event entities');
        }
        break;
      case 'delete':
        if (Permission::allowedIfIsOrganizer($account, $entity->get('grouping')->entity) ||
          Permission::allowedIfIsManager($account, $entity->get('grouping')->entity)) {
          return AccessResult::allowedIfHasPermission($account, 'delete event entities');
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
