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
    $header['id'] = $this->t('Grouping ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    if ($entity->access('view', \Drupal::currentUser())) {
      $row['id'] = $entity->id();
      $row['name'] = $this->l(
        $entity->label(),
        new Url(
          'entity.grouping.edit_form', array(
            'grouping' => $entity->id(),
          )
        )
      );
      return $row + parent::buildRow($entity);
    }
  }

}
