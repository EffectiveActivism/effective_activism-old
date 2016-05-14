<?php

/**
 * @file
 * Contains \Drupal\ea_data\Form\DataForm.
 */

namespace Drupal\ea_data\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Data edit forms.
 *
 * @ingroup ea_data
 */
class DataForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_data\Entity\Data */
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
        drupal_set_message($this->t('Created data.', [
          '%label' => $entity->label(),
        ]));
        break;
      default:
        drupal_set_message($this->t('Saved data.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.data.canonical', ['data' => $entity->id()]);
  }

}
