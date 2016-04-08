<?php

/**
 * @file
 * Contains \Drupal\ea_events\Entity\Event.
 */

namespace Drupal\ea_events\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Event entities.
 */
class EventViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['event']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Event'),
      'help' => $this->t('The Event ID.'),
    );

    return $data;
  }

}
