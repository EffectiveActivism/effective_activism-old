<?php

namespace Drupal\ea_events;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Event entities.
 *
 * @ingroup ea_events
 */
class EventListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Event ID');
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
        $entity->id(),
        new Url(
          'entity.event.edit_form', array(
            'event' => $entity->id(),
          )
        )
      );
      return $row + parent::buildRow($entity);
    }
  }

}
