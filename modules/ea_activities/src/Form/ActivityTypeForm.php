<?php

/**
 * @file
 * Contains \Drupal\ea_activities\Form\ActivityTypeForm.
 */

namespace Drupal\ea_activities\Form;

use Drupal\ea_data\Entity\DataType;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ActivityTypeForm.
 *
 * @package Drupal\ea_activities\Form
 */
class ActivityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $activity_type = $this->entity;
    $organization = $activity_type->organization;
    $groupings = $activity_type->groupings;
    // Get data types.
    $data_types = array();
    if (!empty($activity_type->data_types)) {
      $data_types = array_map(function ($data_type) {
        return DataType::load($data_type['target_id']);
      }, $activity_type->data_types);
    }
    // Get organizations.
    $organizations = array();
    $organizations = array_reduce(Grouping::getManagedGroupings(FALSE), function ($result, $grouping) {
      $result[$grouping->id()] = $grouping->get('name')->getValue()[0]['value'];
      return $result;
    }, array());
    // Get available groupings.
    $available_groupings = array();
    if (!empty($organization)) {
      $available_groupings = array_reduce(Grouping::load($organization)->getRelatives(TRUE), function ($result, $grouping) {
        $result[$grouping->id()] = $grouping->get('name')->getValue()[0]['value'];
        return $result;
      }, array());
    }
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $activity_type->label(),
      '#description' => $this->t("Label for the Activity type."),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $activity_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\ea_activities\Entity\ActivityType::load',
      ),
      '#disabled' => !$activity_type->isNew(),
    );
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $activity_type->description,
      '#description' => $this->t("Description for the Activity type."),
      '#required' => FALSE,
    );
    $form['data_types'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'data_type',
      '#title' => $this->t('Data types'),
      '#default_value' => $data_types,
      '#tags' => TRUE,
      '#description' => $this->t("Data types available for the Activity type."),
      '#required' => FALSE,
    );
    $form['organization'] = array(
      '#type' => 'select',
      '#title' => $this->t('Organization'),
      '#default_value' => $organization,
      '#tags' => TRUE,
      '#description' => $this->t("The organization that the Activity type is available for. Once this option is saved, it cannot be changed."),
      '#options' => $organizations,
      '#required' => TRUE,
      '#disabled' => !empty($organization),
    );
    $form['groupings'] = array(
      '#type' => 'select',
      '#title' => $this->t('Groupings'),
      '#default_value' => $groupings,
      '#description' => $this->t("The groupings the Activity type is available for."),
      '#options' => $available_groupings,
      '#multiple' => TRUE,
      '#required' => FALSE,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $activity_type = $this->entity;
    $status = $activity_type->save();
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Activity type.', [
          '%label' => $activity_type->label(),
        ]));
        break;
      default:
        drupal_set_message($this->t('Saved the %label Activity type.', [
          '%label' => $activity_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($activity_type->urlInfo('collection'));
  }
}
