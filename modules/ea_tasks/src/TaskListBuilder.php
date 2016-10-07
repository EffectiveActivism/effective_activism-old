<?php

namespace Drupal\ea_tasks;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Task entities.
 *
 * @ingroup ea_tasks
 */
class TaskListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Task ID');
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
        $entity->label(),
        new Url(
          'entity.task.edit_form', array(
            'task' => $entity->id(),
          )
        )
      );
      return $row + parent::buildRow($entity);
    }
  }

}
