<?php

namespace Drupal\ea_events\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Event repeater edit forms.
 *
 * @ingroup ea_events
 */
class EventRepeaterForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_events\Entity\EventRepeater */
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
        drupal_set_message($this->t('Created the %label Event repeater.', [
          '%label' => $entity->id(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Event repeater.', [
          '%label' => $entity->id(),
        ]));
    }
    $form_state->setRedirect('entity.event_repeater.canonical', ['event_repeater' => $entity->id()]);
  }

}
