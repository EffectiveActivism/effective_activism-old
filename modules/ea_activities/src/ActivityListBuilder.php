<?php

namespace Drupal\ea_activities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Activity entities.
 *
 * @ingroup ea_activities
 */
class ActivityListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Activity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ea_activities\Entity\Activity */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->id(),
      new Url(
        'entity.activity.edit_form', array(
          'activity' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
