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
    if ($entity->access('view', \Drupal::currentUser())) {
      $organization = Grouping::load($entity->organization);
      $row['label'] = $entity->label();
      $row['import'] = $entity->importname();
      $row['organization'] = \Drupal::l(
        $organization->get('name')->value,
        new Url(
          'entity.grouping.canonical', [
            'grouping' => $organization->id(),
          ]
        )
      );
      return $row + parent::buildRow($entity);
    }
  }

}
