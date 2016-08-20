<?php

namespace Drupal\ea_events;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Event repeater entity.
 *
 * @see \Drupal\ea_events\Entity\EventRepeater.
 */
class EventRepeaterAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ea_events\Entity\EventRepeaterInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view published event repeater entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit event repeater entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete event repeater entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add event repeater entities');
  }

}
