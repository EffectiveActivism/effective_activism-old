<?php

namespace Drupal\ea_imports;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\ea_groupings\Entity\Grouping;

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
    $header['created'] = $this->t('Created');
    $header['grouping'] = $this->t('Grouping');
    $header['events'] = $this->t('Events');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['created'] = \DateTime::createFromFormat('U', $entity->getCreatedTime())->format('d/m Y H:i');
    $row['grouping'] = $this->l(
      $entity->get('grouping')->entity->getTitle(),
      new Url(
        'entity.grouping.canonical', [
          'grouping' => $entity->get('grouping')->entity->id(),
        ]
      )
    );
    $row['events'] = $entity->get('events')->count();
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
      $grouping_ids = Grouping::getAllGroupingsByUser(\Drupal::currentUser(), FALSE);
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
