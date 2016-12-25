<?php

namespace Drupal\ea_groupings;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\ea_groupings\Entity\Grouping;

/**
 * Defines a class to build a listing of Grouping entities.
 *
 * @ingroup ea_groupings
 */
class GroupingListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = $this->t('Title');
    $header['parent'] = $this->t('Parent');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = $this->l(
      $entity->getTitle(),
      new Url(
        'entity.grouping.canonical', [
          'grouping' => $entity->id(),
        ]
      )
    );
    $row['parent'] = empty($entity->get('parent')->entity) ? '' : $this->l(
      $entity->get('parent')->entity->getTitle(),
      new Url(
        'entity.grouping.canonical', [
          'grouping' => $entity->get('parent')->entity->id(),
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
      $grouping_ids = Grouping::getAllGroupingsByUser(\Drupal::currentUser(), FALSE);
      $query->condition('id', $grouping_ids, 'IN');
    }
    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    $result = $query->execute();
    return $result;
  }

}
