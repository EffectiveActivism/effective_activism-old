<?php

/**
 * @file
 * Contains \Drupal\ea_data\Form\DataTypeForm.
 */

namespace Drupal\ea_data\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DataTypeForm.
 *
 * @package Drupal\ea_data\Form
 */
class DataTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $data_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $data_type->label(),
      '#description' => $this->t("Label for the Data type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $data_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\ea_data\Entity\DataType::load',
      ),
      '#disabled' => !$data_type->isNew(),
    );

    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $data_type->description,
      '#description' => $this->t("Description for the Data type."),
      '#required' => FALSE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $data_type = $this->entity;
    $status = $data_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Data type.', [
          '%label' => $data_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Data type.', [
          '%label' => $data_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($data_type->urlInfo('collection'));
  }

}
