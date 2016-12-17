<?php

namespace Drupal\ea_groupings;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

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
    if ($entity->access('view', \Drupal::currentUser())) {
      $row['title'] = $this->l(
        $entity->getTitle(),
        new Url(
          'entity.grouping.canonical', array(
            'grouping' => $entity->id(),
          )
        )
      );
      $row['parent'] = empty($entity->get('parent')->entity) ? '' : $this->l(
        $entity->get('parent')->entity->getTitle(),
        new Url(
          'entity.grouping.canonical', array(
            'grouping' => $entity->get('parent')->entity->id(),
          )
        )
      );
      return $row + parent::buildRow($entity);
    }
  }

}
