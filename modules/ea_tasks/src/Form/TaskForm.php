<?php

namespace Drupal\ea_tasks\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Task edit forms.
 *
 * @ingroup ea_tasks
 */
class TaskForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_tasks\Entity\Task */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->setNewRevision();
    $status = parent::save($form, $form_state);
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Task.', [
          '%label' => $entity->getType(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Task.', [
          '%label' => $entity->getType(),
        ]));
    }
    $form_state->setRedirect('entity.task.canonical', ['task' => $entity->id()]);
  }

}
