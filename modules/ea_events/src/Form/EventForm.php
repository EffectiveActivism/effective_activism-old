<?php

/**
 * @file
 * Contains \Drupal\ea_events\Form\EventForm.
 */

namespace Drupal\ea_events\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Event edit forms.
 *
 * @ingroup ea_events
 */
class EventForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_events\Entity\Event */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Event.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Event.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.event.canonical', ['event' => $entity->id()]);
  }

}
