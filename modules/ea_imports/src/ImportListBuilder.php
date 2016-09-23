<?php

namespace Drupal\ea_imports;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Import entities.
 *
 * @ingroup ea_imports
 */
class ImportListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Import ID');
    $header['grouping'] = $this->t('Grouping');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ea_imports\Entity\Import */
    $row['id'] = $entity->id();
    $row['grouping'] = $this->l(
      $this->t('Edit'),
      new Url(
        'entity.import.edit_form', array(
          'import' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
