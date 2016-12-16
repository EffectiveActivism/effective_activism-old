<?php

namespace Drupal\ea_imports;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Import entities.
 *
 * @ingroup ea_imports
 */
class ImportListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Import ID');
    $header['grouping'] = $this->t('Grouping');
    $header['events'] = $this->t('Events');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ea_imports\Entity\Import */
    $row['id'] = $entity->id();
    $row['grouping'] = $this->l(
      $entity->get('grouping')->entity->get('name')->value,
      new Url(
        'entity.grouping.canonical', [
          'grouping' => $entity->get('grouping')->entity->id(),
        ]
      )
    );
    $row['events'] = $entity->get('events')->count();
    return $row + parent::buildRow($entity);
  }

}
