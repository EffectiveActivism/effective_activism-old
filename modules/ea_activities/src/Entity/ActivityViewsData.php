<?php

/**
 * @file
 * Contains \Drupal\ea_activities\Entity\Activity.
 */

namespace Drupal\ea_activities\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Activity entities.
 */
class ActivityViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['activity']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Activity'),
      'help' => $this->t('The Activity ID.'),
    );

    return $data;
  }

}
