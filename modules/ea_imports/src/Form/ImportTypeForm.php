<?php

namespace Drupal\ea_imports\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ImportTypeForm.
 *
 * @package Drupal\ea_imports\Form
 */
class ImportTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $import_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $import_type->label(),
      '#description' => $this->t("Label for the Import type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $import_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\ea_imports\Entity\ImportType::load',
      ],
      '#disabled' => !$import_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $import_type = $this->entity;
    $status = $import_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Import type.', [
          '%label' => $import_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Import type.', [
          '%label' => $import_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($import_type->urlInfo('collection'));
  }

}
