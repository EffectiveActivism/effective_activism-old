<?php

namespace Drupal\ea_results;

use Drupal\ea_results\Entity\ResultType;
use Drupal\ea_permissions\Permission;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Result entity.
 *
 * @see \Drupal\ea_results\Entity\Result.
 */
class ResultAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return Permission::allowedIfIsManager($account, Grouping::load($entity->type->entity->get('organization')));
        }
        else {
          $gids = $entity->type->entity->get('groupings');
          $groupings = empty($gids) ? [] : Grouping::loadMultiple(array_keys($gids));
          return Permission::allowedIfInGroupings($account, $groupings);
        }
      case 'update':
        $gids = $entity->type->entity->get('groupings');
        $groupings = empty($gids) ? [] : Grouping::loadMultiple(array_keys($gids));
        return Permission::allowedIfInGroupings($account, $groupings);

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
    $result_type = empty($entity_bundle) ? NULL : ResultType::load($entity_bundle);
    $gids = empty($result_type) ? [] : $result_type->get('groupings');
    $groupings = empty($gids) ? [] : Grouping::loadMultiple(array_keys($gids));
    return Permission::allowedIfInGroupings($account, $groupings);
  }

}
