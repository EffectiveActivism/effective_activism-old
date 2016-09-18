<?php

namespace Drupal\ea_groupings\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Grouping entities.
 */
class GroupingViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $data['grouping']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Grouping'),
      'help' => $this->t('The Grouping ID.'),
    );
    return $data;
  }

}
