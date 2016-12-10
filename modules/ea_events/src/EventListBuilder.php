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
    $header['date'] = $this->t('Date');
    $header['start_time'] = $this->t('Start time');
    $header['end_time'] = $this->t('End time');
    $header['title'] = $this->t('Title');
    $header['grouping'] = $this->t('Group');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    if ($entity->access('view', \Drupal::currentUser())) {
      $row['date'] = \DateTime::createFromFormat('Y-m-d\TH:i:s', $entity->get('start_date')->value)->format('d/m Y');
      $row['start_time'] = \DateTime::createFromFormat('Y-m-d\TH:i:s', $entity->get('start_date')->value)->format('H:i');
      $row['end_time'] = \DateTime::createFromFormat('Y-m-d\TH:i:s', $entity->get('end_date')->value)->format('H:i');
      $row['title'] = $this->l(
        empty($entity->get('title')->value) ? $this->t('View') : $entity->get('title')->value,
        new Url(
          'entity.event.canonical',
          [
            'event' => $entity->id(),
          ]
        )
      );
      $row['grouping'] = $this->l(
        $entity->get('grouping')->entity->get('name')->value,
        new Url(
          'entity.grouping.canonical',
          [
            'grouping' => $entity->get('grouping')->entity->id(),
          ]
        )
      );
      return $row + parent::buildRow($entity);
    }
  }

}
