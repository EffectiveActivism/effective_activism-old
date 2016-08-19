<?php

namespace Drupal\ea_events\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Event repeater entities.
 */
class EventRepeaterViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['event_repeater']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Event repeater'),
      'help' => $this->t('The Event repeater ID.'),
    );

    return $data;
  }

}
