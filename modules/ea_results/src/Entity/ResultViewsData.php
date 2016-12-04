<?php

namespace Drupal\ea_results\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Result entities.
 */
class ResultViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $data['result']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Result'),
      'help' => $this->t('The Result ID.'),
    );
    return $data;
  }

}
