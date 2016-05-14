<?php

/**
 * @file
 * Contains \Drupal\ea_people\Form\PersonForm.
 */

namespace Drupal\ea_people\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Person edit forms.
 *
 * @ingroup ea_people
 */
class PersonForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_people\Entity\Person */
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
        drupal_set_message($this->t('Created the %label Person.', [
          '%label' => $entity->label(),
        ]));
        break;
      default:
        drupal_set_message($this->t('Saved the %label Person.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.person.canonical', ['person' => $entity->id()]);
  }
}
