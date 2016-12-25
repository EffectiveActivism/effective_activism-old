<?php

namespace Drupal\ea_results;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\ea_groupings\Entity\Grouping;

/**
 * Provides a listing of Result type entities.
 */
class ResultTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Result type');
    $header['import'] = $this->t('Import name');
    $header['organization'] = $this->t('Organization');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $organization = Grouping::load($entity->organization);
    $row['label'] = $entity->label();
    $row['import'] = $entity->importname();
    $row['organization'] = \Drupal::l(
      $organization->getTitle(),
      new Url(
        'entity.grouping.canonical', [
          'grouping' => $organization->id(),
        ]
      )
    );
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->sort($this->entityType->getKey('id'));
    // Filter entities for non-admin users.
    if (\Drupal::currentUser()->id() !== '1') {
      $grouping_ids = Grouping::getAllOrganizationsManagedByUser(\Drupal::currentUser(), FALSE);
      $query->condition('grouping', $grouping_ids, 'IN');
    }
    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    $result = $query->execute();
    return $result;
  }

}
