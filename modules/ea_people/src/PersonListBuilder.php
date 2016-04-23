<?php

/**
 * @file
 * Contains \Drupal\ea_people\PersonListBuilder.
 */

namespace Drupal\ea_people;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Person entities.
 *
 * @ingroup ea_people
 */
class PersonListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Person ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ea_people\Entity\Person */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.person.edit_form', array(
          'person' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }
}
