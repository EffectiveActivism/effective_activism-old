<?php

namespace Drupal\ea_results;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Result entities.
 *
 * @ingroup ea_results
 */
class ResultListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Result ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ea_results\Entity\Result */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->id(),
      new Url(
        'entity.result.edit_form', array(
          'result' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
