<?php

namespace Drupal\ea_results;

use Drupal\ea_permissions\Permission;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the ResultType entity.
 *
 * @see \Drupal\ea_results\Entity\ResultType.
 */
class ResultTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        $gids = array_merge(array_keys($entity->get('groupings')), [$entity->get('organization')]);
        $groupings = empty($gids) ? [] : Grouping::loadMultiple($gids);
        return Permission::allowedIfInGroupings($account, $groupings);

      case 'update':
        $gid = $entity->get('organization');
        $organization = empty($gid) ? [] : Grouping::load($gid);
        return Permission::allowedIfIsManager($account, $organization);

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
    return Permission::allowedIfIsManagerInAnyGroupings($account);
  }

}
